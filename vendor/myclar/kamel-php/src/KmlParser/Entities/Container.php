<?php
declare(strict_types = 1);

namespace KamelPhp\KmlParser\Entities;

use KamelPhp\KmlParser\EntityTagNames;
use KamelPhp\XmlElement\Element;

abstract class Container extends Feature {

	/** @var Element */
	protected $element;

	public function __construct(Element $element) {
		parent::__construct($element);
	}

	/**
	 * @return Folder[]
	 */
	public function getFolders(): array {
		if (!$this->hasFolders()) {
			return array();
		}

		return array_map(function (Element $element) {
			return new Folder($element);
		}, $this->element->getChildren(EntityTagNames::Folder));
	}

	public function hasFolders(): bool {
		return $this->element->hasChild(EntityTagNames::Folder);
	}

	public function getDocument(): Document {
		return new Document($this->element->getChild(EntityTagNames::Document));
	}

	/**
	 * @return Document[]
	 */
	public function getDocuments(): array {
		if (!$this->hasDocuments()) {
			return array();
		}

		return array_map(function (Element $element) {
			return new Document($element);
		}, $this->element->getChildren(EntityTagNames::Document));
	}

	public function hasDocuments(): bool {
		return $this->element->hasChild(EntityTagNames::Document);
	}

	/**
	 * @return Placemark[]
	 */
	public function getPlacemarks(): array {
		return array_map(function (Element $element) {
			return new Placemark($element);
		}, $this->element->getChildren(EntityTagNames::Placemerk));
	}

	public function hasPlacemarks(): bool {
		return $this->element->hasChild(EntityTagNames::Placemerk);
	}
}
