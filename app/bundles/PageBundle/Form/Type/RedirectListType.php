<?php

declare(strict_types=1);

namespace MailVotech\PageBundle\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * @extends AbstractType<array<mixed>>
 */
final class RedirectListType extends AbstractType
{
    public function configureOptions(OptionsResolver $resolver): void
    {
        $choices = [
            'mailvotech.page.form.redirecttype.permanent'     => Response::HTTP_MOVED_PERMANENTLY,
            'mailvotech.page.form.redirecttype.temporary'     => Response::HTTP_FOUND,
            'mailvotech.page.form.redirecttype.303_temporary' => Response::HTTP_SEE_OTHER,
            'mailvotech.page.form.redirecttype.307_temporary' => Response::HTTP_TEMPORARY_REDIRECT,
            'mailvotech.page.form.redirecttype.308_permanent' => Response::HTTP_PERMANENTLY_REDIRECT,
        ];

        $resolver->setDefaults([
            'choices'     => $choices,
            'expanded'    => false,
            'multiple'    => false,
            'label'       => 'mailvotech.page.form.redirecttype',
            'label_attr'  => ['class' => 'control-label'],
            'placeholder' => false,
            'required'    => false,
            'attr'        => ['class' => 'form-control'],
            'feature'     => 'all',
        ]);

        $resolver->setDefined(['feature']);
    }

    public function getParent(): string
    {
        return ChoiceType::class;
    }
}
