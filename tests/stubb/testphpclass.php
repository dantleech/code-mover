<?php

namespace Acme\CmsBundle\Form\Admin;

use Acme\OldFormBundle\Form\Form;
use Acme\CmsBundle\Entity\Site;
use Acme\OldFormBundle\Form\TextField;
use Acme\OldFormBundle\Form\ChoiceField;
use Acme\OldFormBundle\Form\CheckboxField;
use Acme\OldFormBundle\Form\FieldGroup;

use Acme\CmsBundle\Inheritance\Form\InheritanceField;
use Symfony\Component\Validator\ValidatorInterface;

use Acme\CmsBundle\Doctrine\ValueTransformer\EntityToIDTransformer;


use Doctrine\ORM\EntityManager;

class SiteCreateForm extends Form
{
    public function __construct($name, array $options = array())
    {
        $this->addRequiredOption('security_context');
        $this->addRequiredOption('entity_manager');
        $this->addRequiredOption('extension_manager');
        parent::__construct($name, $options);
    }

    public function configure()
    {
        // PREPARE THE SITE CHOICES
        if ($this->getOption('security_context')->isGranted('ROLE_PLATFORM_ADMIN')) {
            $companies = $this->getOption('entity_manager')->getRepository('Acme\CrmBundle\Entity\Company')->findBy(array());
            $companiesForSelect = array('' => null);

            foreach ($companies as $company) {
                $companiesForSelect[$company->getId()] = $company->getCompanyName();
            }
            $companyTransformer = new EntityToIDTransformer(array(
                'em' => $this->getOption('entity_manager'),
                'className' => 'Acme\CrmBundle\Entity\Company'
            ));
            $this->add(new ChoiceField('company', array(
                'choices' => $companiesForSelect,
                'value_transformer' => $companyTransformer,
            )));
            $this->add(new TextField('bundleName'));
        }

        // PREPARE THE EXTENSION CHOICES
        $extensions = $this->getOption('extension_manager')->getExtensions();
        $extensionChoices = array();
        foreach ($extensions as $extension) {
            $extensionChoices[$extension->getName()] = $extension->getTitle();
        }

        $this->add(new TextField('title'));
        $this->add(new TextField('host'));
        $this->add(new TextField('localesAsCsv'));
        $this->add(new TextField('defaultLocale'));

        $statisticsEngines = array(
            Site::STATS_NONE  =>  "",
            Site::STATS_GOOGLE  =>  Site::STATS_GOOGLE,
            Site::STATS_ACME  =>  Site::STATS_YPROXIMITE,
        );
        $this->add(new ChoiceField('statisticsEngine', array('choices' => $statisticsEngines)));

        $this->add(new CheckboxField('enableExternalLinks'));
        $this->add(new CheckboxField('enableDir'));
    }
}
