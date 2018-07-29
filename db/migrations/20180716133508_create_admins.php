<?php


use Phinx\Migration\AbstractMigration;

class CreateAdmins extends AbstractMigration
{
    /**
     * Change Method.
     *
     * Write your reversible migrations using this method.
     *
     * More information on writing migrations is available here:
     * http://docs.phinx.org/en/latest/migrations.html#the-abstractmigration-class
     *
     * The following commands can be used in this method and Phinx will
     * automatically reverse them when rolling back:
     *
     *    createTable
     *    renameTable
     *    addColumn
     *    addCustomColumn
     *    renameColumn
     *    addIndex
     *    addForeignKey
     *
     * Any other destructive changes will result in an error when trying to
     * rollback the migration.
     *
     * Remember to call "create()" or "update()" and NOT "save()" when working
     * with the Table class.
     */
    public function change()
    {
		$this->table('admins')
			->addColumn('login', 'string')
			->addColumn('color', 'string')
			->addColumn('first_name', 'string')
			->addColumn('last_name', 'string')
			->addColumn('middle_name', 'string')
			->addColumn('phone', 'string')
			->addColumn('photo_extension', 'string')
			->addColumn('has_photo_cropped', 'boolean', ['default' => 0])
			->addColumn('salary', 'integer', ['null' => true, 'signed' => false])
			->addColumn('rights', 'string', ['limit' => 1000])
			->addColumn('updated_at', 'datetime')
			->create();
    }
}
