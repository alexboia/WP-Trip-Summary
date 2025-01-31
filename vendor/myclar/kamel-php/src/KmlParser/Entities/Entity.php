<?php
declare(strict_types = 1);

namespace KamelPhp\KmlParser\Entities;

use KamelPhp\XmlElement\Element;

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
