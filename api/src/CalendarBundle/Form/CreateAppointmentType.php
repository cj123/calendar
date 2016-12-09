<?php

namespace CalendarBundle\Form;

use CalendarBundle\Entity\Appointment;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\DateTimeType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Class CreateAppointmentType
 * @package CalendarBundle\Form
 * @author Callum Jones <cj@icj.me>
 */
class CreateAppointmentType extends AbstractType
{
    /**
     * @param FormBuilderInterface $builder
     * @param array $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add("text", TextareaType::class,    [ "required" => true ]);
        $builder->add("start", DateTimeType::class,   [ "required" => true, "widget" => "single_text", "date_format" => "yyyy-MM-dd HH:mm" ]);
        $builder->add("finish", DateTimeType::class,  [ "required" => true, "widget" => "single_text", "date_format" => "yyyy-MM-dd HH:mm" ]);
        $builder->add("calendar", IntegerType::class, [ "required" => true ]);
        $builder->add("timezone", TextareaType::class, [ "required" => true ]);
        $builder->add("owner", TextareaType::class, [ "required" => true ]);
    }

    /**
     * @param OptionsResolver $resolver
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            "data_class" => Appointment::class,
            "csrf_protection" => false,
        ]);
    }
}
