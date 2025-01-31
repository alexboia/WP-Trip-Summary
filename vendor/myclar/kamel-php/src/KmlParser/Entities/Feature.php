<?php
declare(strict_types = 1);

namespace KamelPhp\KmlParser\Entities {

    use KamelPhp\KmlParser\EntityTagNames;
    use KamelPhp\XmlElement\Element;

	abstract class Feature extends Entity {
		public function __construct(Element $element) {
			parent::__construct($element);
		}

		public function getSnippet(): Snippet|null {
			if (!$this->hasSnippet()) {
				return null;
			}

			return new Snippet($this->element->getChild(EntityTagNames::Snippet));
		}

		public function getSnippetText(): string|null {
			$snippet = $this->getSnippet();
			if ($snippet != null) {
				return $snippet->getText();
			} else {
				return null;
			}
		}

		public function  hasSnippet() : bool {
			return $this->element->hasChild(EntityTagNames::Snippet);
		}

		public function getTimeStamp(): TimeStamp|null {
			if (!$this->hasTimeStamp()) {
				return null;
			}

			return new TimeStamp($this->element->getChild(EntityTagNames::TimeStamp));
		}

		public function hasTimeStamp(): bool {
			return $this->element->hasChild(EntityTagNames::TimeStamp);
		}

		public function getTimeSpan(): TimeSpan|null {
			if (!$this->hasTimeSpan()) {
				return null;
			}

			return new TimeSpan($this->element->getChild(EntityTagNames::TimeSpan));
		}

		public function hasTimeSpan(): bool {
			return $this->element->hasChild(EntityTagNames::TimeSpan);
		}

		public function getExtendedData(): ExtendedData|null {
			if (!$this->hasExtendedData()) {
				return null;
			}
	
			return new ExtendedData($this->element->getChild(EntityTagNames::ExtendedData));
		}
	
		public function hasExtendedData(): bool {
			return $this->element->hasChild(EntityTagNames::ExtendedData);
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