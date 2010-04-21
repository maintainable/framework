<?= "<?php\n" ?>

class <?= $this->className ?> extends ApplicationController
{
    // filters and common variables
    protected function _initialize()
    {
    }
    
<?  foreach ($this->views as $view) : ?>
    public function <?= $view ?>()
    {
    }

<?  endforeach; ?>
}
