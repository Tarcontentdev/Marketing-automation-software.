<?php

declare(strict_types=1);

namespace MailVotechPlugin\MailVotechFocusBundle\Tests\Controller;

use MailVotech\CoreBundle\Test\MailVotechMysqlTestCase;
use MailVotechPlugin\MailVotechFocusBundle\Entity\Focus;
use MailVotechPlugin\MailVotechFocusBundle\Model\FocusModel;
use PHPUnit\Framework\Attributes\PreserveGlobalState;
use PHPUnit\Framework\Attributes\RunInSeparateProcess;
use Symfony\Component\HttpFoundation\Request;

final class FocusPublicControllerFunctionalTest extends MailVotechMysqlTestCase
{
    #[PreserveGlobalState(false)]
    #[RunInSeparateProcess]
    public function testGenerateFocusItemScript(): void
    {
        /** @var FocusModel $focusModel */
        $focusModel = self::getContainer()->get(FocusModel::class);
        $focus      = $this->createFocus('popup');
        $focus->setStyle('bar');
        $focusModel->saveEntity($focus);

        $this->client->request(Request::METHOD_GET, "/focus/{$focus->getId()}.js");
        $response = $this->client->getResponse();
        $this->assertResponseIsSuccessful();
        $content = (string) $response->getContent();

        $this->assertStringContainsString("MailVotechFocus{$focus->getId()}", $content);
        $this->assertStringContainsString("mailvotech_focus_{$focus->getId()}", $content);
        $this->assertStringContainsString("mailvotech_focus_{$focus->getId()}_closed", $content);
        $this->assertStringContainsString("mf-bar-collapser-{$focus->getId()}", $content);
        $this->assertMatchesRegularExpression("/Focus\\.cookies\\.setItem\\(['\"]mailvotech_focus_{$focus->getId()}['\"]\\s*,\\s*-1\\s*,/", $content);
        $this->assertStringNotContainsString('MailVotechJS', $content);
        $this->assertStringNotContainsString('mtc_id', $content);
        $this->assertStringNotContainsString('mailvotech_device_id', $content);
        $this->assertStringNotContainsString('/mtc.js', $content);
        $this->assertStringNotContainsString('/mailvotech-essential.js', $content);
        $this->assertStringNotContainsString('/mailvotech-tracking.js', $content);
    }

    #[PreserveGlobalState(false)]
    #[RunInSeparateProcess]
    public function testInactiveFocusItemScript(): void
    {
        /** @var FocusModel $focusModel */
        $focusModel = self::getContainer()->get(FocusModel::class);
        $focus      = $this->createFocus('popup');
        $focus->setIsPublished(false);
        $focusModel->saveEntity($focus);

        $this->client->request(Request::METHOD_GET, "/focus/{$focus->getId()}.js");
        $response = $this->client->getResponse();
        $this->assertTrue($response->isNotFound());
        $this->assertEmpty($response->getContent());
    }

    private function createFocus(string $name): Focus
    {
        $focus = new Focus();
        $focus->setName($name);
        $focus->setType('link');
        $focus->setStyle('modal');
        $focus->setProperties([
            'bar' => [
                'allow_hide' => 1,
                'push_page'  => 1,
                'sticky'     => 1,
                'size'       => 'large',
                'placement'  => 'top',
            ],
            'modal' => [
                'placement' => 'top',
            ],
            'notification' => [
                'placement' => 'top_left',
            ],
            'page'            => [],
            'animate'         => 0,
            'link_activation' => 1,
            'colors'          => [
                'primary'     => '4e5d9d',
                'text'        => '000000',
                'button'      => 'fdb933',
                'button_text' => 'ffffff',
            ],
            'content' => [
                'headline'        => null,
                'tagline'         => null,
                'link_text'       => null,
                'link_url'        => null,
                'link_new_window' => 1,
                'font'            => 'Arial, Helvetica, sans-serif',
                'css'             => null,
            ],
            'when'                  => 'immediately',
            'timeout'               => null,
            'frequency'             => 'everypage',
            'stop_after_conversion' => 1,
        ]);

        return $focus;
    }
}
