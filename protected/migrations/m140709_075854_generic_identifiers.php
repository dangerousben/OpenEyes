<?php

class m140709_075854_generic_identifiers extends OEMigration
{
	const NHS_NUM_SYSTEM = 'nhs_num';
	const HOS_NUM_SYSTEM = 'hos_num';
	const GNC_SYSTEM = 'gnc';
	const PRACTICE_CODE_SYSTEM = 'practice_code';
	const CCG_CODE_SYSTEM = 'ccg_code';

	public function safeUp()
	{
		$this->createOETable(
			'identifier_type',
			array(
				'id' => 'pk',
				'class' => 'string not null',
				'system' => 'string not null',
				'label' => 'string not null',
				'unique' => 'boolean not null default true',
				'constraint identifier_type_system unique (system)',
			)
		);

		$this->createOETable(
			'patient_identifier',
			array(
				'id' => 'pk',
				'resource_id' => 'integer unsigned not null',
				'type_id' => 'integer not null',
				'value' => 'string not null',
				'active' => 'boolean not null default true',
				'constraint patient_identifier_rid_fk foreign key (resource_id) references patient (id)',
				'constraint patient_identifier_tid_fk foreign key (type_id) references identifier_type (id)',
			)
		);

		$this->createOETable(
			'gp_identifier',
			array(
				'id' => 'pk',
				'resource_id' => 'integer unsigned not null',
				'type_id' => 'integer not null',
				'value' => 'string not null',
				'active' => 'boolean not null default true',
				'constraint gp_identifier_rid_fk foreign key (resource_id) references gp (id)',
				'constraint gp_identifier_tid_fk foreign key (type_id) references identifier_type (id)',
			)
		);

		$this->createOETable(
			'practice_identifier',
			array(
				'id' => 'pk',
				'resource_id' => 'integer unsigned not null',
				'type_id' => 'integer not null',
				'value' => 'string not null',
				'active' => 'boolean not null default true',
				'constraint practice_identifier_rid_fk foreign key (resource_id) references practice (id)',
				'constraint practice_identifier_tid_fk foreign key (type_id) references identifier_type (id)',
			)
		);

		$this->createOETable(
			'commissioning_body_identifier',
			array(
				'id' => 'pk',
				'resource_id' => 'integer unsigned not null',
				'type_id' => 'integer not null',
				'value' => 'string not null',
				'active' => 'boolean not null default true',
				'constraint commissioning_body_identifier_rid_fk foreign key (resource_id) references commissioning_body (id)',
				'constraint commissioning_body_identifier_tid_fk foreign key (type_id) references identifier_type (id)',
			)
		);

		$this->insert('identifier_type', array('class' => 'services\Patient', 'system' => self::NHS_NUM_SYSTEM, 'label' => 'NHS Number'));
		$this->insert('identifier_type', array('class' => 'services\Patient', 'system' => self::HOS_NUM_SYSTEM, 'label' => 'Hospital Number'));
		$this->insert('identifier_type', array('class' => 'services\Gp', 'system' => self::GNC_SYSTEM, 'label' => 'General National Code'));
		$this->insert('identifier_type', array('class' => 'services\Practice', 'system' => self::PRACTICE_CODE_SYSTEM, 'label' => 'Organisation Code'));
		$this->insert('identifier_type', array('class' => 'services\CommissioningBody', 'system' => self::CCG_CODE_SYSTEM, 'label' => 'Organisation Code'));

		$type_ids = $this->getTypeIds();

		$this->execute("insert into patient_identifier (resource_id, type_id, value) select id, :type_id, nhs_num from patient", array(':type_id' => $type_ids[self::NHS_NUM_SYSTEM]));
		$this->execute("insert into patient_identifier (resource_id, type_id, value) select id, :type_id, hos_num from patient", array(':type_id' => $type_ids[self::HOS_NUM_SYSTEM]));
		$this->execute("insert into gp_identifier (resource_id, type_id, value) select id, :type_id, nat_id from gp", array(':type_id' => $type_ids[self::GNC_SYSTEM]));
		$this->execute("insert into practice_identifier (resource_id, type_id, value) select id, :type_id, code from practice", array(':type_id' => $type_ids[self::PRACTICE_CODE_SYSTEM]));
		$this->execute("insert into commissioning_body_identifier (resource_id, type_id, value) select id, :type_id, code from commissioning_body", array(':type_id' => $type_ids[self::CCG_CODE_SYSTEM]));

		$this->dropColumn('patient', 'pas_key');
		$this->dropColumn('patient', 'hos_num');
		$this->dropColumn('patient', 'nhs_num');
		$this->dropColumn('gp', 'obj_prof');
		$this->dropColumn('gp', 'nat_id');
		$this->dropColumn('practice', 'code');
		$this->dropColumn('commissioning_body', 'code');
	}

	public function safeDown()
	{
		$this->addColumn('patient', 'pas_key', 'int(10) unsigned DEFAULT NULL');
		$this->addColumn('patient', 'hos_num', 'varchar(40) DEFAULT NULL');
		$this->addColumn('patient', 'nhs_num', 'varchar(40) DEFAULT NULL');
		$this->addColumn('gp', 'obj_prof', 'varchar(20) DEFAULT NULL');
		$this->addColumn('gp', 'nat_id', 'varchar(20) DEFAULT NULL');
		$this->addColumn('practice', 'code', 'varchar(64) DEFAULT NULL');
		$this->addColumn('commissioning_body', 'code', 'varchar(16) DEFAULT NULL');

		$type_ids = $this->getTypeIds();

		$this->execute('update patient p ' .
			'inner join patient_identifier h on h.resource_id = p.id and h.type_id = :hos_num_id ' .
			'inner join patient_identifier n on n.resource_id = p.id and n.type_id = :nhs_num_id ' .
			'set p.pas_key = h.value, p.hos_num = h.value, p.nhs_num = n.value',
			array(':hos_num_id' => $type_ids[self::HOS_NUM_SYSTEM], ':nhs_num_id' => $type_ids[self::NHS_NUM_SYSTEM])
		);

		$this->execute('update gp g ' .
			'inner join gp_identifier i on i.resource_id = g.id and i.type_id = :type_id ' .
			'set g.nat_id = i.value',
			array(':type_id' => $type_ids[self::GNC_SYSTEM])
		);

		$this->execute('update practice p ' .
			'inner join practice_identifier i on i.resource_id = p.id and i.type_id = :type_id ' .
			'set p.code = i.value',
			array(':type_id' => $type_ids[self::PRACTICE_CODE_SYSTEM])
		);

		$this->execute('update commissioning_body c ' .
			'inner join commissioning_body_identifier i on i.resource_id = c.id and i.type_id = :type_id ' .
			'set c.code = i.value',
			array(':type_id' => $type_ids[self::CCG_CODE_SYSTEM])
		);

		$this->dropOETable('patient_identifier');
		$this->dropOETable('gp_identifier');
		$this->dropOETable('practice_identifier');
		$this->dropOETable('commissioning_body_identifier');
		$this->dropOETable('identifier_type');
	}

	private function getTypeIds()
	{
		$rows = $this->dbConnection->createCommand()->select(array('id', 'system'))->from('identifier_type')->queryAll();
		$ids = array();
		foreach ($rows as $row) $ids[$row['system']] = $row['id'];
		return $ids;
	}
}
