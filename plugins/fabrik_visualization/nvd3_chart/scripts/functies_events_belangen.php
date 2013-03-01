<?php

// Checkbox sum

$db = JFactory::getDbo();
$query = $db->getQuery(true);

// Get labels
$query->select('params')->from('i2qtd_fabrik_elements')->where('id = 41');
$db->setQuery($query);
$params = $db->loadResult();
$params = json_decode($params);
$sub_options = ($params->sub_options);
$sub_values = ($sub_options->sub_values);

$sub_labels = ($sub_options->sub_labels);
$labels = array_combine($sub_values, $sub_labels);

$query->clear();
$query->select('functies_events_belangen')->from('fab_userinfo');
$db->setQuery($query);
$data = array();
$rows = $db->loadColumn();

foreach ($rows as $row)
{
	$vals = json_decode($row);
	foreach ($vals as $val)
	{
		if (!is_null($val))
		{
			if (!array_key_exists($val, $data))
			{
				$o = new stdClass;
				$o->label = $labels[$val];
				$o->value = 1;
				$data[$val] = $o;
			}
			else
			{
				$data[$val]->value ++;
			}
		}
	}
}
$this->data = new stdClass;
$this->data->key = 'todo2';
$this->data->values = array();

foreach ($data as $data)
{
	$this->data->values[] = $data;
}
$this->data = array($this->data);
