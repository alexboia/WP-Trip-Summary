<?php
declare(strict_types = 1);

namespace StepanDalecky\KmlParser\Entities {

    use StepanDalecky\XmlElement\Element;

	abstract class Feature extends Entity {
		public function __construct(Element $element) {
			parent::__construct($element);
		}

		public function getId(): string {
			return $this->element->getAttribute('id');
		}
	
		public function hasId(): bool {
			return $this->element->hasAttribute('id');
		}

		public function getStyleUrl(): string {
			return $this->element->getChild('styleUrl')->getValue();
		}

		public function hasStyleUrl(): bool {
			return $this->element->hasChild('styleUrl');
		}

		public function getName(): string {
			if (!$this->hasName()) {
				return '';
			}

			return $this->element->getChild('name')->getValue();
		}
	
		public function hasName(): bool {
			return $this->element->hasChild('name');
		}

		public function getDescription(): string {
			if (!$this->hasDescription()) {
				return '';
			}

			return $this->element->getChild('description')->getValue();
		}
	
		public function hasDescription(): bool {
			return $this->element->hasChild('description');
		}

		public function getIsOpen(): bool {
			if (!$this->hasIsOpen()) {
				return false;
			}

			return intval($this->element->getChild('open')->getValue()) === 1;
		}
	
		public function hasIsOpen(): bool {
			return $this->element->hasChild('open');
		}

		public function getVisibility(): bool {
			if (!$this->hasVisibility()) {
				return false;
			}

			return intval($this->element->getChild('visibility')->getValue()) === 1;
		}

		public function hasVisibility(): bool {
			return $this->element->hasChild('visibility');
		}

		public function getAddress(): string {
			if (!$this->hasAddress()) {
				return '';
			}

			return $this->element->getChild('address')->getValue();
		}

		public function hasAddress(): bool {
			return $this->element->hasChild('address');
		}

		public function getPhoneNumber(): string {
			if (!$this->hasPhoneNumber()) {
				return '';
			}

			return $this->element->getChild('phoneNumber')->getValue();
		}

		public function hasPhoneNumber(): bool {
			return $this->element->hasChild('phoneNumber');
		}
	}
}