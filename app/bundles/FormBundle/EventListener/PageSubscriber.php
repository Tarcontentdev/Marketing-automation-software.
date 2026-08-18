<?php

namespace MailVotech\FormBundle\EventListener;

use MailVotech\CoreBundle\DTO\TokenFormatOptions;
use MailVotech\CoreBundle\Helper\BuilderTokenHelperFactory;
use MailVotech\CoreBundle\Security\Permissions\CorePermissions;
use MailVotech\FormBundle\FormEvents;
use MailVotech\FormBundle\Model\FormModel;
use MailVotech\PageBundle\Event\PageBuilderEvent;
use MailVotech\PageBundle\Event\PageDisplayEvent;
use MailVotech\PageBundle\PageEvents;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Contracts\Translation\TranslatorInterface;

final class PageSubscriber implements EventSubscriberInterface
{
    private string $formRegex = '{form=(.*?)}';

    public function __construct(
        private readonly FormModel $formModel,
        private readonly BuilderTokenHelperFactory $builderTokenHelperFactory,
        private readonly TranslatorInterface $translator,
        private readonly CorePermissions $security,
    ) {
    }

    public static function getSubscribedEvents(): array
    {
        return [
            PageEvents::PAGE_ON_DISPLAY => ['onPageDisplay', 0],
            PageEvents::PAGE_ON_BUILD   => ['onPageBuild', 0],
        ];
    }

    /**
     * Add forms to available page tokens.
     */
    public function onPageBuild(PageBuilderEvent $event): void
    {
        if ($event->abTestWinnerCriteriaRequested()) {
            // add AB Test Winner Criteria
            $formSubmissions = [
                'group'    => 'mailvotech.form.abtest.criteria',
                'label'    => 'mailvotech.form.abtest.criteria.submissions',
                'event'    => FormEvents::ON_DETERMINE_SUBMISSION_RATE_WINNER,
            ];
            $event->addAbTestWinnerCriteria('form.submissions', $formSubmissions);
        }

        if ($event->tokensRequested($this->formRegex)) {
            $tokenHelper = $this->builderTokenHelperFactory->getBuilderTokenHelper('form');
            $tokenFilter = $event->getTokenFilter();
            $tokens      = $tokenHelper->getFormattedTokens(
                $this->formRegex,
                TokenFormatOptions::simplePrefix('mailvotech.form.form'),
                'label' === $tokenFilter['target'] ? $tokenFilter['filter'] : '',
            );
            if ([] !== $tokens) {
                $event->addTokens($tokens);
            }
        }
    }

    public function onPageDisplay(PageDisplayEvent $event): void
    {
        $content = $event->getContent();
        $page    = $event->getPage();
        $regex   = '/'.$this->formRegex.'/i';

        preg_match_all($regex, $content, $matches);

        if (count($matches[0])) {
            foreach ($matches[1] as $id) {
                $form = $this->formModel->getEntity($id);
                if (null !== $form
                    && (
                        $form->isPublished(false)
                        || $this->security->hasEntityAccess(
                            'form:forms:viewown', 'form:forms:viewother', $form->getCreatedBy()
                        )
                    )
                ) {
                    $formHtml = ($form->isPublished()) ? $this->formModel->getContent($form) :
                        '<div class="mailvotechform-error">'.
                        $this->translator->trans('mailvotech.form.form.pagetoken.notpublished').
                        '</div>';

                    // add the hidden page input
                    $pageInput = "\n<input type=\"hidden\" name=\"mailvotechform[mailvotechpage]\" value=\"{$page->getId()}\" />\n";
                    $formHtml  = preg_replace('#</form>#', $pageInput.'</form>', $formHtml);

                    // pouplate get parameters
                    $this->formModel->populateValuesWithGetParameters($form, $formHtml);
                    $content = str_replace('{form='.$id.'}', $formHtml, $content);
                } else {
                    $content = str_replace('{form='.$id.'}', '', $content);
                }
            }
        }
        $event->setContent($content);
    }
}
