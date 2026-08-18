<?php

declare(strict_types=1);

namespace MailVotech\CampaignBundle\Controller;

use MailVotech\CampaignBundle\Entity\Campaign;
use MailVotech\CampaignBundle\Entity\Event;
use MailVotech\CampaignBundle\Form\Type\CampaignImportType;
use MailVotech\CoreBundle\Controller\AbstractFormController;
use MailVotech\CoreBundle\Event\EntityImportAnalyzeEvent;
use MailVotech\CoreBundle\Event\EntityImportEvent;
use MailVotech\CoreBundle\Event\EntityImportUndoEvent;
use MailVotech\CoreBundle\Helper\DateTimeHelper;
use MailVotech\CoreBundle\Helper\ImportHelper;
use MailVotech\CoreBundle\Helper\PathsHelper;
use MailVotech\CoreBundle\Helper\UserHelper;
use MailVotech\CoreBundle\Service\FlashBag;
use MailVotech\FormBundle\Entity\Action;
use MailVotech\FormBundle\Entity\Field;
use MailVotech\FormBundle\Entity\Form;
use Psr\Log\LoggerInterface;
use Psr\Log\LogLevel;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Form\FormError;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\HttpFoundation\File\Exception\FileException;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Contracts\Service\Attribute\Required;

final class ImportController extends AbstractFormController
{
    // Steps of the import
    public const STEP_UPLOAD_ZIP      = 1;

    public const STEP_PROGRESS_BAR    = 2;

    public const STEP_IMPORT_FROM_ZIP = 3;

    private UserHelper $userHelper;

    private RequestStack $requestStack;

    private LoggerInterface $logger;

    private PathsHelper $pathsHelper;

    private FormFactoryInterface $formFactory;

    #[Required]
    public function autowireImportController(
        UserHelper $userHelper,
        RequestStack $requestStack,
        LoggerInterface $logger,
        PathsHelper $pathsHelper,
        FormFactoryInterface $formFactory,
    ): void {
        $this->userHelper   = $userHelper;
        $this->requestStack = $requestStack;
        $this->logger       = $logger;
        $this->pathsHelper  = $pathsHelper;
        $this->formFactory  = $formFactory;
    }

    public function newAction(): Response
    {
        if (!$this->security->isGranted('campaign:imports:create')) {
            $this->throwAccessDenied();
        }

        $session  = $this->requestStack->getSession();
        $filePath = $session->get('mailvotech.campaign.import.file');

        if ($filePath && file_exists($filePath)) {
            @unlink($filePath);
            $this->logger->info("Removed leftover import file on refresh: {$filePath}");
        }

        $this->resetImport();

        $form = $this->formFactory->create(CampaignImportType::class, [], [
            'action' => $this->generateUrl('mailvotech_campaign_import_action', ['objectAction' => 'upload']),
        ]);

        return $this->delegateView([
            'viewParameters'  => [
                'form'          => $form->createView(),
                'mailvotechContent' => 'campaignImport',
            ],
            'contentTemplate' => '@MailVotechCampaign/Import/import.html.twig',
        ]);
    }

    public function uploadAction(Request $request): Response
    {
        if (!$this->security->isGranted('campaign:imports:create')) {
            $this->throwAccessDenied();
        }

        $fullPath = $this->pathsHelper->getImportCampaignsPath().'/'.$this->getImportFileName();
        $fileName = $this->getImportFileName();

        $importDir = $this->pathsHelper->getImportCampaignsPath();
        $form      = $this->formFactory->create(CampaignImportType::class, [], [
            'action' => $this->generateUrl('mailvotech_campaign_import_action', ['objectAction' => 'upload']),
        ]);

        // Handle cancel action
        if ($this->isFormCancelled($form)) {
            $this->resetImport();
            $this->removeImportFile($fullPath);
            $this->logger->log(LogLevel::WARNING, "Import for file {$fullPath} was canceled.");

            return $this->newAction();
        }

        // Validate form before processing
        if (!$this->isFormValid($form)) {
            $this->logger->error('No file uploaded.');
            $form->addError(new FormError($this->translator->trans('mailvotech.campaign.import.incorrectfile', [], 'validators')));
        } else {
            // Retrieve uploaded file
            $fileData = $request->files->get('campaign_import')['campaignFile'] ?? null;

            if (!$fileData) {
                $this->logger->error('No file uploaded.');
                $form->addError(new FormError($this->translator->trans('mailvotech.campaign.import.nofile', [], 'validators')));
            } else {
                // Set progress to 0 before import starts
                $this->requestStack->getSession()->set('mailvotech.campaign.import.step', self::STEP_PROGRESS_BAR);
                $this->requestStack->getSession()->set('mailvotech.campaign.import.progress', 0);
                $this->requestStack->getSession()->remove('mailvotech.campaign.import.summary');
                try {
                    // Ensure the import directory exists
                    (new Filesystem())->mkdir($importDir, 0755);

                    // Remove existing file if it exists
                    if (file_exists($fullPath)) {
                        if (!unlink($fullPath)) {
                            $this->logger->error("Failed to delete existing file before new upload: {$fullPath}");
                        }
                    }

                    // Move uploaded file
                    $fileData->move($importDir, $fileName);

                    // Update session with the new file and progress reset
                    $this->requestStack->getSession()->set('mailvotech.campaign.import.file', $fullPath);
                    $this->logger->info("File successfully uploaded: {$fullPath}");

                    return $this->redirectToRoute('mailvotech_campaign_import_action', ['objectAction' => 'progress']);
                } catch (FileException $e) {
                    $this->logger->error('File upload failed: '.$e->getMessage());

                    $form->addError(new FormError(
                        $this->translator->trans(
                            str_contains($e->getMessage(), 'upload_max_filesize')
                                ? 'mailvotech.lead.import.filetoolarge'
                                : 'mailvotech.lead.import.filenotreadable',
                            [],
                            'validators'
                        )
                    ));
                }
            }
        }

        return $this->delegateView([
            'viewParameters'  => [
                'mailvotechContent' => 'campaignImport',
                'form'          => $form->createView(),
            ],
            'contentTemplate' => '@MailVotechCampaign/Import/import.html.twig',
        ]);
    }

    /**
     * Cancels import by removing the uploaded file.
     */
    public function cancelAction(): RedirectResponse
    {
        if (!$this->security->isGranted('campaign:imports:create')) {
            $this->throwAccessDenied();
        }

        $filePath = $this->requestStack->getSession()->get('mailvotech.campaign.import.file');

        if (is_string($filePath)) {
            $this->removeImportFile($filePath);
        }

        $this->resetImport();
        $this->addFlashMessage('mailvotech.campaign.notice.import.canceled', [], FlashBag::LEVEL_NOTICE);

        return $this->redirectToRoute('mailvotech_campaign_import_action', ['objectAction' => 'new']);
    }

    private function resetImport(): void
    {
        $this->requestStack->getSession()->set('mailvotech.campaign.import.file', null);
        $this->requestStack->getSession()->set('mailvotech.campaign.import.step', self::STEP_UPLOAD_ZIP);
        $this->requestStack->getSession()->set('mailvotech.campaign.import.progress', 0);
        $this->requestStack->getSession()->remove('mailvotech.campaign.import.analyzeSummary');
    }

    private function removeImportFile(string $filepath): void
    {
        if (file_exists($filepath) && is_readable($filepath)) {
            unlink($filepath);

            $this->logger->log(LogLevel::WARNING, "File {$filepath} was removed.");
        }
    }

    /**
     * Generates unique import directory name inside the cache dir if not stored in the session.
     * If it exists in the session, returns that one.
     */
    private function getImportFileName(): string
    {
        $session  = $this->requestStack->getSession();
        $fileName = $session->get('mailvotech.campaign.import.file');

        if ($fileName && !str_contains($fileName, '/')) {
            return $fileName;
        }

        $uniqueId = bin2hex(random_bytes(8));
        $fileName = sprintf('%s_%s.zip', (new DateTimeHelper())->toUtcString('YmdHis'), $uniqueId);

        $session->set('mailvotech.campaign.import.file', $fileName);

        return $fileName;
    }

    public function progressAction(ImportHelper $importHelper): Response
    {
        $session       = $this->requestStack->getSession();
        $session->get('mailvotech.campaign.import.progress', 0);
        $step          = $session->get('mailvotech.campaign.import.step', self::STEP_PROGRESS_BAR);
        $fullPath      = $session->get('mailvotech.campaign.import.file');

        // If there's no valid file, show an error
        if (!$fullPath || !file_exists($fullPath)) {
            if (self::STEP_UPLOAD_ZIP !== $step) {
                $this->addFlashMessage('mailvotech.campaign.import.nofile', [], FlashBag::LEVEL_ERROR, 'validators');
            }
            $this->resetImport();

            return $this->redirectToRoute('mailvotech_campaign_import_action', ['objectAction' => 'new']);
        }

        if (self::STEP_PROGRESS_BAR === $step) {
            $analyzeSummary = $this->analyzeData($importHelper, $fullPath);

            if ([] === $analyzeSummary) {
                $this->addFlashMessage('mailvotech.campaign.import.nofile', [], FlashBag::LEVEL_ERROR, 'validators');
                $this->removeImportFile($fullPath);
                $this->resetImport();

                return $this->redirectToRoute('mailvotech_campaign_import_action', ['objectAction' => 'new']);
            }
            $session->set('mailvotech.campaign.import.step', self::STEP_IMPORT_FROM_ZIP);
            $session->set('mailvotech.campaign.import.analyzeSummary', $analyzeSummary);

            return $this->delegateView([
                'viewParameters' => [
                    'importProgress'  => 50,
                    'analyzeSummary'  => $analyzeSummary,
                    'mailvotechContent'   => 'campaignImport',
                ],
                'contentTemplate' => '@MailVotechCampaign/Import/progress.html.twig',
            ]);
        }
        try {
            $fileData      = $importHelper->readZipFile($fullPath);
            $userId        = $this->userHelper->getUser()->getId();
            $importSummary = [];

            $importActions = $this->requestStack->getCurrentRequest()->get('importAction', []);

            $importHelper->recursiveRemoveEmailaddress($fileData);

            // Loop through importActions and clean UUIDs for 'create' actions
            foreach ($fileData as &$group) {
                foreach ($importActions as $entityType => $entities) {
                    if (in_array($entityType, [Event::ENTITY_NAME, Field::ENTITY_NAME, Action::ENTITY_NAME], true)) {
                        continue;
                    }
                    if (!isset($group[$entityType])) {
                        continue;
                    }

                    foreach ($entities as $entityUuid => $action) {
                        if ('create' !== $action) {
                            continue;
                        }

                        foreach ($group[$entityType] as &$item) {
                            if (isset($item['uuid']) && (int) $item['uuid'] === (int) $entityUuid) {
                                if (Campaign::ENTITY_NAME == $entityType) {
                                    foreach ($group[Event::ENTITY_NAME] as &$eventItem) {
                                        $eventItem['uuid'] = '';
                                    }
                                }
                                if (Form::ENTITY_NAME == $entityType) {
                                    if (isset($group[Field::ENTITY_NAME])) {
                                        foreach ($group[Field::ENTITY_NAME] as &$fieldItem) {
                                            $fieldItem['uuid'] = '';
                                        }
                                    }
                                    if (isset($group[Action::ENTITY_NAME])) {
                                        foreach ($group[Action::ENTITY_NAME] as &$actionItem) {
                                            $actionItem['uuid'] = '';
                                        }
                                    }
                                }
                                $item['uuid'] = '';
                                break;
                            }
                        }
                    }
                }
            }

            foreach ($fileData as $entity) {
                $event  = new EntityImportEvent(Campaign::ENTITY_NAME, $entity, $userId);
                $this->dispatcher->dispatch($event);
                $summary = $event->getStatus();
                if ([] !== $summary) {
                    $importSummary[] = $summary;
                }
            }

            foreach ($importSummary as $summary) {
                foreach ([EntityImportEvent::NEW, EntityImportEvent::UPDATE] as $status) {
                    if (!isset($summary[$status][Campaign::ENTITY_NAME])) {
                        continue;
                    }

                    $campaignData    = $summary[$status][Campaign::ENTITY_NAME];
                    $campaignName    = $campaignData['names'][0] ?? 'Unknown';
                    $campaignId      = $campaignData['ids'][0] ?? 0;

                    $this->addFlashMessage(
                        'mailvotech.campaign.notice.import.finished',
                        [
                            '%id%'   => $campaignId,
                            '%name%' => htmlspecialchars($campaignName, ENT_QUOTES, 'UTF-8'),
                        ]
                    );
                }
            }

            $this->removeImportFile($fullPath);
            $session->set('mailvotech.campaign.import.summary', $importSummary);
            $this->resetImport();
        } catch (\RuntimeException $e) {
            $this->logger->error($e->getMessage());
            $this->addFlashMessage('mailvotech.campaign.import.nofile', [], FlashBag::LEVEL_ERROR, 'validators');

            $this->removeImportFile($fullPath);
            $importSummary = [
                EntityImportEvent::ERRORS => [$e->getMessage()],
            ];
        }

        return $this->delegateView([
            'viewParameters' => [
                'importProgress'  => 100,
                'importSummary'   => $importSummary,
                'mailvotechContent'   => 'campaignImport',
            ],
            'contentTemplate' => '@MailVotechCampaign/Import/progress.html.twig',
        ]);
    }

    /**
     * @return array<int|string, array<string, mixed>>
     */
    private function analyzeData(ImportHelper $importHelper, string $fullPath): array
    {
        try {
            $fileData = $importHelper->readZipFile($fullPath);
        } catch (\RuntimeException $e) {
            $this->logger->error($e->getMessage());
            $this->removeImportFile($fullPath);

            return [
                [
                    'errors' => [
                        'messages' => [$e->getMessage()],
                    ],
                ],
            ];
        }

        $allData = [];
        foreach ($fileData as $entityData) {
            $mergedSummary = [];
            foreach ($entityData as $key => $data) {
                if (empty($data)) {
                    continue;
                }

                $event = new EntityImportAnalyzeEvent($key, $data);
                $this->dispatcher->dispatch($event);
                $summary = $event->getSummary();

                foreach ($summary as $status => $entities) {
                    if ('errors' === $status) {
                        // Accumulate errors into a flat array
                        $mergedSummary['errors'] = array_merge(
                            $mergedSummary['errors'] ?? [],
                            is_array($entities) ? $entities : [$entities]
                        );
                        continue;
                    }
                    foreach ($entities as $entityName => $info) {
                        if (!isset($mergedSummary[$status][$entityName])) {
                            $mergedSummary[$status][$entityName] = [
                                'names'   => [],
                                'uuids'   => [],
                            ];
                        }

                        $mergedSummary[$status][$entityName]['names'] = array_merge(
                            $mergedSummary[$status][$entityName]['names'],
                            $info['names'] ?? []
                        );
                        $mergedSummary[$status][$entityName]['uuids'] = array_merge(
                            $mergedSummary[$status][$entityName]['uuids'],
                            $info['uuids'] ?? []
                        );
                    }
                }
            }
            if ([] !== $mergedSummary) {
                $allData[] = $mergedSummary;
            }
        }

        return $allData;
    }

    public function undoAction(): JsonResponse
    {
        if (!$this->security->isGranted('campaign:imports:delete')) {
            $this->throwAccessDenied();
        }

        $session         = $this->requestStack->getSession();
        $importSummaries = $session->get('mailvotech.campaign.import.summary', []);

        $hasUndoData = false;

        foreach ($importSummaries as $summary) {
            $updates  = $summary[EntityImportEvent::UPDATE] ?? [];
            $newItems = $summary[EntityImportEvent::NEW] ?? [];

            // Only trigger undo if there are no updates and we have new items
            if (empty($updates) && !empty($newItems)) {
                foreach ($newItems as $entityType => $data) {
                    if (!empty($data['ids'])) {
                        $undoEvent = new EntityImportUndoEvent($entityType, $data);
                        $this->dispatcher->dispatch($undoEvent);
                        $hasUndoData = true;
                    }
                }
            }
        }

        if ($hasUndoData) {
            $this->logger->info('Undo import triggered for Campaign.');
            $this->addFlashMessage('mailvotech.campaign.notice.import.undo');
        } else {
            $this->addFlashMessage('mailvotech.campaign.notice.import.undo_no_data');
        }

        return new JsonResponse(['flashes' => $this->getFlashContent()]);
    }
}
