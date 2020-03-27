<?php
declare(strict_types=1);

namespace LeanpubBookClub\Infrastructure\Symfony\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\FormBuilderInterface;

final class RequestAccessTokenForm extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add(
                'emailAddress',
                EmailType::class,
                [
                    'label' => 'request_access_token_form.email_address.label',
                    'help' => 'request_access_token_form.email_address.help'
                ]
            )
            ->add(
                'request_access_token',
                SubmitType::class,
                [
                    'label' => 'request_access_token_form.request_access_token.label'
                ]
            );
    }
}