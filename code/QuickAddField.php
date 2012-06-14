<?php

class QuickAddField extends OptionsetField {

	protected
		$controller,
		$labelField = 'Title',
		$idField = 'ID',
		$fieldType = 'OptionSetField',
		$addTitle = 'Create new',
		$className,
		$field,
		$defaultsProperties,
		$selectAll = true,
		$defaultItems = array();

	function __construct(DataObject $controller,$name,$title = null,$className = null,$source = array(),$addTitle = null,$defaultsProperties = array(), $form = null) {
		$hasOne = false;
		if (!$title) {
			$this->title = self::name_to_label($name);
		}
		if (!$className) {
			if (substr($name,-2) == 'ID') {
				$name = substr($name,0,-2);
			}
			if ((!$hasOne = $className = $controller->has_one($name)) && (!$className = $controller->belongs_to($name)) && (($settings = $controller->has_many($name)) || ($settings = $controller->many_many($name)))) {
				if (is_array($settings)) {
					$className = $settings[1];
				} else {
					$className = $settings;
				}
				$this->fieldType = 'CheckboxSetField';
			}
			if (!$className) {
				trigger_error('Couldn\'t determine class type from field name "' . $name . '". Please define the class name.');
			}
			if ($hasOne) {
				$name .= 'ID';
			}
		}
		if (!class_exists($className)) {
			trigger_error($className . ' class doesn\'t exist');
		}
		$this->setDefaults($defaultsProperties);
		$this->className = $className;
		$this->controller = $controller;
		parent::__construct($name,$title,$source,null,$form);
	}

	function setLabelField($var) {
		$this->labelField = $var;
	}

	function setSelectAll(bool $val) {
		$this->selectAll = $val;
	}

	function setDefaults($defaults = array()) {
		$this->defaultsProperties = $defaults;
	}

	function mergeDefaults($defaults = array()) {
		$this->defaultsProperties = array_merge($this->defaultsProperties,$defaults);
	}

	function setDefault($name,$val) {
		$this->defaultsProperties[$name] = $val;
	}

	function setAddTitle($val) {
		$this->addTitle = $val;
	}

	function saveInto($record) {
		if ($this->fieldType == 'CheckboxSetField') {
			CheckboxSetField::saveInto($record);
		}
		else {
			parent::saveInto($record);
		}
	}

	function performDisabledTransformation() {
		if ($this->fieldType == 'CheckboxSetField') {
			CheckboxSetField::performDisabledTransformation();
		}
		else {
			parent::performDisabledTransformation();
		}
	}

	function performReadonlyTransformation() {
		if ($this->fieldType == 'CheckboxSetField') {
			CheckboxSetField::performReadonlyTransformation();
		}
		else {
			parent::performReadonlyTransformation();
		}
	}

	function dataValue() {
		if ($this->fieldType == 'CheckboxSetField') {
			return CheckboxSetField::dataValue();
		}
		return parent::dataValue();
	}

	function getSource() {
		if (is_array($this->source) && empty($this->source)) {
			$this->source = DataObject::get($this->className);
			if (!$this->source) {
				$this->source = array();
			}
			elseif ($this->fieldType == 'OptionSetField') {
				$this->source = $this->source->toDropdownMap($this->idField,$this->labelField);
			}
		}
		return $this->source;
	}

	function setValue($value,$obj = null) {
		if ($this->fieldType == 'CheckboxSetField') {
			CheckboxSetField::setValue($value,$obj);
		}
		else {
			parent::setValue($value);
		}
	}

	function Field() {
		$this->getSource();
		//die($this->Link('findOrAdd'));
		//Requirements::javascript(THIRDPARTY_DIR."/jquery-livequery/jquery.livequery.js");

		Requirements::javascript(MOD_QA_DIR .'/javascript/quickAddField.js');
		Requirements::CSS(MOD_QA_DIR .'/css/quickaddfield.css');

		if ($this->fieldType == 'CheckboxSetField') {
			$selectAll = '';
			if ($this->selectAll) {
				$selectAll = '<a class="selectAll" href="#">Select All</a>';
			}
			return $selectAll . CheckboxSetField::Field();
		}
		return parent::Field();
	}

	function FieldHolder() {
		$addForm = new FieldGroup(array(
			$textField = new TextField($this->Name() . '_quickAdd',$this->addTitle),
			new LiteralField('', '<a class="quickadd" href="#">Add &#43;</a>')
		));

		$textField->addExtraClass('quickadd');
		return '<div class="quickAddHolder">' . parent::FieldHolder() . $addForm->FieldHolder() . '</div>';
	}

	function findOrAdd($request) {
		if ($title = $request->getVar('Title')) {
			if (!$obj = DataObject::get_one($this->className,$this->labelField . " = '" . Convert::raw2sql($title) . "'")) {
				$obj = new $this->className($this->defaultsProperties);
				$obj->{$this->labelField} = $title;
				$obj->write();
			}
			return Convert::array2json(array(
				'ID' => $obj->ID,
				'Title' => $obj->{$this->labelField}
			));
		}
	}

	function delete($request) {
		$id = $request->getVar('Id');
		$success = 0;
		// If request id is non falsey
		if ($id > 0 && is_numeric($id) && $id == (int)$id) {
			// Grab object if it exists
			if ($obj = DataObject::get_by_id($this->className,$id)) {
				// Delete object
				$obj->delete();
				$success = 1;
			}
		}
		return '{"success":' . $success . '}';
	}

	function edit($request) {
		$id = $request->getVar('Id');
		$content = $request->getVar('Title');
		$json = array('success' => 0);
		// If request id is non falsey
		if ($content && $id > 0 && is_numeric($id) && $id == (int)$id) {
			if (($obj = DataObject::get_by_id($this->className,$id)) && !DataObject::get_one($this->className,$this->labelField . " = '" . Convert::raw2sql($content) . "' AND ID != " . $id)) {
				// Change title to new given title
				$obj->{$this->labelField} = $content;
				$obj->write();
				$json['Title'] = $obj->{$this->labelField};
				$json['success'] = 1;
			}
		}
		return Convert::array2json($json);
	}

}
