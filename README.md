# Code Mover

Small library for migrating code, its sort of analagous to database migrations
but instead of applying changes to a database, you apply it to code.

*Note this is a proof of concept* at the moment and has much work to be done.

This allows you to write and refine code migrations for massive API changes and
refactorings.

````php
// app/CodeMoverMigrations/FormFieldMigrator.php

use DTL\CodeMover\AbstractMigrator;
use DTL\CodeMover\MoverFile;

class FormFieldMigrator extends AbstractMigrator
{
    protected $fieldTypeMap = array(
    );

    public function getName()
    {
        return 'form_fields';
    }

    public function getDependencies()
    {
        return array('namespaces');
    }

    public function accepts(MoverFile $file)
    {
        return $file->nameMatches('*Form.php');
    }

    public function migrate(MoverFile $file)
    {
        $file->findLine('.*foobar.*')->replace('foobar', 'barfoo');

        $file->findLines(array(
            'use .*?Field;',
            'use .*Group;'
        ))->delete();

        $file->findLines('$this->add\(new .*?Field')
            ->replace('\$this->add\(new (.*?)Field\(\'(.*?)\'', function ($matches) {
                $fieldType = strtolower($matches[1]);
                return '$this->add('.$matches[2].', '.$fieldType.'\'';
            });
    }
}
````

The above migrator will:

- Replace `foobar` with `barfoo` on lines matching regex `.*foobar.*`
- Only process files matching the regex pattern `*Form.php`;
- Delete all lines that match either `use .*Field;` or `use .*Group`
- Will replace lines like `$this->add(new TextAreaField('field_name')` with `$this->add('field_name', 'textarea');`

You can run it on some code:

````bash
php bin/codemover.php migrate ~/myproject/app/CodeMoverMigrations \
    --path ~/myproject/src/Bundle1 \
    --path ~/myproject/src/Bundle2 \
    --name "*CreateForm.php"
````

This will:

- Run all the migration classes in `~/myproject/app/CodeMoverMigrations`
- On the code contained in `~/myproject/src/Bundle1` and `~/myproject/src/Bundle2`
- Only on filenames matching `*CreateForm.php`

And generate some output like:

````bash
Adding migrator: FormFieldMigrator
Adding migrator: NamespacesMigrator
Resolved migrator order: namespaces, form_fields
Migrator "namespaces" accepts file "/home/daniel/www/yProximite/yProx/src/Ylly/CmsBundle/Form/Admin/SiteCreateForm.php"
  -namespace Ylly\CmsBundle\Form\Admin;
  +namespace Ylly\CmsBundle\Form\Type\Admin;
Migrator "form_fields" accepts file "/home/daniel/www/yProximite/yProx/src/Ylly/CmsBundle/Form/Admin/SiteCreateForm.php"
  -use Ylly\OldFormBundle\Form\TextField;
  -use Ylly\OldFormBundle\Form\ChoiceField;
  -use Ylly\OldFormBundle\Form\CheckboxField;
  -use Ylly\OldFormBundle\Form\FieldGroup;
  -use Ylly\CmsBundle\Inheritance\Form\InheritanceField;
  -            $this->add(new ChoiceField('company', array(
  +            $this->add(company, choice', array(
  -            $this->add(new TextField('bundleName'));
  +            $this->add(bundleName, text'));
  -        $this->add(new TextField('title'));
  -        $this->add(new TextField('host'));
  -        $this->add(new TextField('localesAsCsv'));
  -        $this->add(new TextField('defaultLocale'));
  +        $this->add(title, text'));
  +        $this->add(host, text'));
  +        $this->add(localesAsCsv, text'));
  +        $this->add(defaultLocale, text'));
  -        $this->add(new ChoiceField('statisticsEngine', array('choices' => $statisticsEngines)));
  +        $this->add(statisticsEngine, choice', array('choices' => $statisticsEngines)));
  -        $this->add(new CheckboxField('enableExternalLinks'));
  -        $this->add(new CheckboxField('enableDir'));
  +        $this->add(enableExternalLinks, checkbox'));
  +        $this->add(enableDir, checkbox'));
````
