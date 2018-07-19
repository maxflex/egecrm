<?php


use Phinx\Migration\AbstractMigration;

class DropColumnsFromUsers extends AbstractMigration
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
    public function up()
    {
		$this->table('users')
			->removeColumn('login')
			->removeColumn('worktime')
			->removeColumn('banned')
			->removeColumn('color')
			->removeColumn('first_name')
			->removeColumn('last_name')
			->removeColumn('middle_name')
			->removeColumn('token')
			->removeColumn('phone')
			->removeColumn('login_count')
			->removeColumn('last_action_time')
			->removeColumn('last_action_link')
			->removeColumn('photo_extension')
			->removeColumn('has_photo_cropped')
			->removeColumn('salary')
			->removeColumn('rights')
			->removeColumn('updated_at')
			->save();
    }

	public function down($value='')
	{
		// code...
	}
}
