<?= "<?php\n" ?>

class <?= $this->migrationName ?> extends Mad_Model_Migration_Base 
{
    public function up()
    {
<? if ($this->tableName): ?>
        $t = $this->createTable('<?= $this->tableName ?>');
            // $t->column('name', 'string');
        $t->end();
<? endif ?>
    }

    public function down()
    {
<? if ($this->tableName): ?>
        $this->dropTable('<?= $this->tableName ?>');
<? endif ?>
    }
}