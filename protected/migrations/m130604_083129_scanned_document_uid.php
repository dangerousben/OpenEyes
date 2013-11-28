<?php

class m130604_083129_scanned_document_uid extends OEMigration
{
	public function up() {
		$this->createUidTable();
	}

	public function down() {
		$this->dropTable('scanned_document_uid');
	}

	/**
	 * Creates a table to identify unique IDs with patient IDs.
	 */
	private function createUidTable()
	{
		$this->createTable('scanned_document_uid', array_merge(array(
					'id' => 'int(10) unsigned NOT NULL AUTO_INCREMENT',
					'pid' => 'varchar(40) NOT NULL',
				), $this->getDefaults('scanned_document_uid')), 'ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_bin');
	}
}