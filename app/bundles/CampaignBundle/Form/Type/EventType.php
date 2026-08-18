<?php

namespace MailVotech\CampaignBundle\Form\Type;

use MailVotech\CampaignBundle\Executioner\Scheduler\Mode\Optimized as OptimizedScheduler;
use MailVotech\CoreBundle\Form\EventListener\CleanFormSubscriber;
use MailVotech\CoreBundle\Form\Type\ButtonGroupType;
use MailVotech\CoreBundle\Form\Type\FormButtonsType;
use MailVotech\CoreBundle\Form\Type\PropertiesTrait;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\DateTimeType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * @extends AbstractType<mixed>
 */
final class EventType extends AbstractType
{
    use PropertiesTrait;

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $masks = [];

        $builder->add(
            'name',
            TextType::class,
            [
                'label'      => 'mailvotech.core.name',
                'label_attr' => ['class' => 'control-label'],
                'attr'       => ['class' => 'form-control'],
                'required'   => false,
            ]
        );

        $builder->add(
            'anchor',
            HiddenType::class,
            [
                'label' => false,
            ]
        );

        if (in_array($options['data']['eventType'], ['action', 'condition'])) {
            $label = 'mailvotech.campaign.form.type';

            $choices = [
                'immediate' => 'mailvotech.campaign.form.type.immediate',
                'interval'  => 'mailvotech.campaign.form.type.interval',
                'date'      => 'mailvotech.campaign.form.type.date',
            ];

            if (in_array($options['data']['type'], OptimizedScheduler::AVAILABLE_FOR_EVENTS)) {
                $choices['optimized'] = 'mailvotech.campaign.form.type.optimized';
            }

            if (isset($options['data']['anchor']) && isset($options['data']['anchorEventType'])
                && 'no' === $options['data']['anchor']
                && 'condition' !== $options['data']['anchorEventType']
                && 'condition' !== $options['data']['eventType']) {
                $label .= '_inaction';

                unset($choices['immediate']);
                $choices['interval'] .= '_inaction';
                $choices['date'] .= '_inaction';
            }
            $default = array_key_first($choices);

            $triggerMode = (empty($options['data']['triggerMode'])) ? $default : $options['data']['triggerMode'];
            $builder->add(
                'triggerMode',
                ButtonGroupType::class,
                [
                    'choices'           => array_flip($choices),
                    'expanded'          => true,
                    'multiple'          => false,
                    'label_attr'        => ['class' => 'control-label'],
                    'label'             => $label,
                    'placeholder'       => false,
                    'required'          => false,
                    'attr'              => [
                        'onchange' => 'MailVotech.campaignToggleTimeframes();',
                        'tooltip'  => 'mailvotech.campaign.form.type.help',
                    ],
                    'data'        => $triggerMode,
                ]
            );

            $builder->add(
                'triggerDate',
                DateTimeType::class,
                [
                    'label'  => false,
                    'attr'   => [
                        'class'       => 'form-control',
                        'preaddon'    => 'ri-calendar-line',
                        'data-toggle' => 'datetime',
                    ],
                    'widget' => 'single_text',
                    'html5'  => false,
                    'format' => 'yyyy-MM-dd HH:mm',
                    'data'   => $this->getTimeValue($options['data'], 'triggerDate'),
                ]
            );

            $data = (!isset($options['data']['triggerInterval']) || '' === $options['data']['triggerInterval']) ? 1 : (int) $options['data']['triggerInterval'];
            $builder->add(
                'triggerInterval',
                IntegerType::class,
                [
                    'label' => false,
                    'attr'  => [
                        'class'    => 'form-control',
                        'preaddon' => 'symbol-hashtag',
                    ],
                    'data'  => $data,
                ]
            );

            $data = (!empty($options['data']['triggerIntervalUnit'])) ? $options['data']['triggerIntervalUnit'] : 'd';
            $builder->add(
                'triggerIntervalUnit',
                ChoiceType::class,
                [
                    'choices'     => [
                        'mailvotech.campaign.event.intervalunit.choice.i' => 'i',
                        'mailvotech.campaign.event.intervalunit.choice.h' => 'h',
                        'mailvotech.campaign.event.intervalunit.choice.d' => 'd',
                        'mailvotech.campaign.event.intervalunit.choice.m' => 'm',
                        'mailvotech.campaign.event.intervalunit.choice.y' => 'y',
                    ],
                    'multiple'          => false,
                    'label_attr'        => ['class' => 'control-label'],
                    'label'             => false,
                    'attr'              => [
                        'class' => 'form-control',
                    ],
                    'placeholder' => false,
                    'required'    => false,
                    'data'        => $data,
                ]
            );

            // I could not get Doctrine TimeType does not play well with Symfony TimeType so hacking this workaround
            $data = $this->getTimeValue($options['data'], 'triggerHour');
            $builder->add(
                'triggerHour',
                TextType::class,
                [
                    'label' => false,
                    'attr'  => [
                        'class'        => 'form-control',
                        'data-toggle'  => 'time',
                        'data-format'  => 'H:i',
                        'autocomplete' => 'off',
                    ],
                    'data'  => ($data) ? $data->format('H:i') : $data,
                ]
            );

            $data = $this->getTimeValue($options['data'], 'triggerRestrictedStartHour');
            $builder->add(
                'triggerRestrictedStartHour',
                TextType::class,
                [
                    'label' => false,
                    'attr'  => [
                        'class'        => 'form-control',
                        'data-toggle'  => 'time',
                        'data-format'  => 'H:i',
                        'autocomplete' => 'off',
                    ],
                    'data'  => ($data) ? $data->format('H:i') : $data,
                ]
            );

            $data = $this->getTimeValue($options['data'], 'triggerRestrictedStopHour');
            $builder->add(
                'triggerRestrictedStopHour',
                TextType::class,
                [
                    'label' => false,
                    'attr'  => [
                        'class'        => 'form-control',
                        'data-toggle'  => 'time',
                        'data-format'  => 'H:i',
                        'autocomplete' => 'off',
                    ],
                    'data'  => ($data) ? $data->format('H:i') : $data,
                ]
            );

            $builder->add(
                'triggerRestrictedDaysOfWeek',
                ChoiceType::class,
                [
                    'label'    => true,
                    'attr'     => [
                        'data-toggle' => 'time',
                        'data-format' => 'H:i',
                    ],
                    'choices'  => [
                        'mailvotech.report.schedule.day.monday'     => 1,
                        'mailvotech.report.schedule.day.tuesday'    => 2,
                        'mailvotech.report.schedule.day.wednesday'  => 3,
                        'mailvotech.report.schedule.day.thursday'   => 4,
                        'mailvotech.report.schedule.day.friday'     => 5,
                        'mailvotech.report.schedule.day.saturday'   => 6,
                        'mailvotech.report.schedule.day.sunday'     => 0,
                        'mailvotech.report.schedule.day.week_days'  => -1,
                    ],
                    'expanded'          => true,
                    'multiple'          => true,
                    'required'          => false,
                ]
            );

            $builder->add(
                'triggerWindow',
                ChoiceType::class,
                [
                    'label'    => false,
                    'choices'  => [
                        'mailvotech.campaign.form.type.trigger_window_day'   => OptimizedScheduler::OPTIMIZED_TIME,
                        'mailvotech.campaign.form.type.trigger_window_week'  => OptimizedScheduler::OPTIMIZED_DAY_AND_TIME,
                    ],
                    'data'              => $options['data']['triggerWindow'] ?? 0,
                    'required'          => false,
                    'expanded'          => true,
                    'placeholder'       => false,
                ]
            );

            $builder->addEventListener(FormEvents::PRE_SUBMIT, function (FormEvent $event): void {
                $data        = $event->getData();
                $triggerMode = $data['triggerMode'] ?? 'immediate';

                // Do not set any trigger window when optimized mode is not used
                if ('optimized' !== $triggerMode) {
                    $data['triggerWindow'] = null;
                    $event->setData($data);
                }
            });
        }

        if (!empty($options['settings']['formType'])) {
            $this->addPropertiesType($builder, $options, $masks);
        }

        $builder->add('type', HiddenType::class);
        $builder->add('eventType', HiddenType::class);
        $builder->add(
            'anchorEventType',
            HiddenType::class,
            [
                'mapped' => false,
                'data'   => $options['data']['anchorEventType'] ?? '',
            ]
        );

        $builder->add(
            'canvasSettings',
            EventCanvasSettingsType::class,
            [
                'label' => false,
            ]
        );

        $update = !empty($options['data']['properties']);
        if (!empty($update)) {
            $btnValue = 'mailvotech.core.form.update';
            $btnIcon  = 'ri-edit-line';
        } else {
            $btnValue = 'mailvotech.core.form.add';
            $btnIcon  = 'ri-add-line';
        }

        $builder->add(
            'buttons',
            FormButtonsType::class,
            [
                'save_text'       => $btnValue,
                'save_icon'       => $btnIcon,
                'save_onclick'    => 'MailVotech.submitCampaignEvent(event)',
                'cancel_onclick'  => 'MailVotech.cancelCampaignEvent(event)',
                'apply_text'      => false,
                'container_class' => 'bottom-form-buttons',
            ]
        );

        $builder->add(
            'campaignId',
            HiddenType::class,
            [
                'mapped' => false,
            ]
        );

        $builder->addEventSubscriber(new CleanFormSubscriber($masks));

        if (!empty($options['action'])) {
            $builder->setAction($options['action']);
        }
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setRequired(['settings']);
    }

    private function getTimeValue(array $data, string $name): ?\DateTime
    {
        if (empty($data[$name])) {
            return null;
        }

        if ($data[$name] instanceof \DateTime) {
            return $data[$name];
        }

        if (is_array($data[$name]) && array_key_exists('date', $data[$name])) {
            return $this->parseTimeValue($data[$name]['date']);
        }
        if (is_string($data[$name])) {
            return $this->parseTimeValue($data[$name]);
        }

        return null;
    }

    private function parseTimeValue(string $value): \DateTime
    {
        $trimmedValue = trim($value);

        if (preg_match('/^\d{1,2}$/', $trimmedValue)) {
            $parsed = \DateTime::createFromFormat('!H', $trimmedValue);
            if (false !== $parsed) {
                return $parsed;
            }
        }

        if (preg_match('/^\d{1,2}:\d{2}$/', $trimmedValue)) {
            $parsed = \DateTime::createFromFormat('!H:i', $trimmedValue);
            if (false !== $parsed) {
                return $parsed;
            }
        }

        return new \DateTime($trimmedValue);
    }

    public function getBlockPrefix(): string
    {
        return 'campaignevent';
    }
}
