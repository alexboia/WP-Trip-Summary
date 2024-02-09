<?php
declare(strict_types = 1);

namespace StepanDalecky\KmlParser\Entities;

use StepanDalecky\XmlElement\Element;

class Entity {
	/** @var Element */
	protected $element;

	public function __construct(Element $element) {
		$this->element = $element;
	}

	public function getElement(): Element {
		return $this->element;
	}
}
