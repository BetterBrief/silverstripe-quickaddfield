<?php

class QuickAddField extends CheckboxSetField {

	protected
		$labelField = 'Title',
		$idField = 'ID',
		$fieldType = 'CheckboxSetField',
		$addTitle = 'Create new',
		$className,
		$field;

	function __construct(DataObject $controller,$name,$title = null,$className = null,$source = array(),$addTitle = null,$form = null) {
		if (!$title) {
			$this->title = self::name_to_label($name);
		}
		if (!$className) {
			if ($className = $controller->has_one($name) || $className = $controller->belongs_to($name)) {
				$this->fieldType = 'OptionsetField';
			}
			elseif ($settings = $controller->has_many($name)) {
				$className = $settings[1];
			}
			elseif ($settings = $controller->many_many($name)) {
				$className = $settings[1];
			}
			else {
				trigger_error('Couldn\'t determine class type');
			}
		}
		$this->className = $className;
		parent::__construct($name,$title,$source,null,$form);
	}

	function setAddTitle($val) {
		$this->addTitle = $val;
	}

	function getSource() {
		if (!$this->source) {
			$this->source = DataObject::get($this->className);
			if (!$this->source) {
				$this->source = array();
			}
		}
		return $this->source;
	}

	function Field() {
		$this->getSource();
		//die($this->Link('findOrAdd'));
		//Requirements::javascript(THIRDPARTY_DIR."/jquery-livequery/jquery.livequery.js");

		Requirements::javascript(MOD_QA_DIR .'/javascript/quickAddField.js');
		Requirements::CSS(MOD_QA_DIR .'/css/quickaddfield.css');

		if ($this->fieldType == 'CheckboxSetField') {
			return parent::Field();
		}
		return OptionsetField::Field();
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
				$obj = new $this->className();
				$obj->{$this->labelField} = $title;
				$obj->write();
			}
			return Convert::array2json(array(
				'ID' => $obj->ID,
				'Title' => $obj->{$this->labelField}
			));
		}
	}

}
