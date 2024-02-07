<?php
declare(strict_types = 1);

namespace StepanDalecky\XmlElement;

use StepanDalecky\XmlElement\Exceptions\UnexpectedXmlStructureException;

class Element
{

	/**
	 * @var \SimpleXMLElement
	 */
	private $xmlElement;

	public function __construct(\SimpleXMLElement $xmlElement)
	{
		$this->xmlElement = $xmlElement;
	}

	public static function fromString(string $xmlString): self
	{
		return new self(new \SimpleXMLElement($xmlString));
	}

	public function getChild(string $name): self
	{
		if (!isset($this->xmlElement->{$name})) {
			throw new UnexpectedXmlStructureException(sprintf(
				'There is no <%s> element nested in <%s> element.',
				$name,
				$this->getName()
			));
		}
		if ($this->xmlElement->{$name}->count() > 1) {
			throw new UnexpectedXmlStructureException(sprintf(
				'There are more <%s> elements nested in <%s>, only one was expected.',
				$name,
				$this->getName()
			));
		}

		/** @var \SimpleXMLElement $nestedXmlElement */
		$nestedXmlElement = $this->xmlElement->{$name};

		return new self($nestedXmlElement);
	}

	/**
	 * @param string $name
	 * @return self[]
	 */
	public function getChildren(string $name): array
	{
		if (!$this->hasChild($name)) {
			throw new UnexpectedXmlStructureException(sprintf(
				'There are no <%s> elements nested in <%s> element.',
				$name,
				$this->getName()
			));
		}

		$elements = [];
		/** @var \SimpleXMLElement $xmlElement */
		foreach ($this->xmlElement->{$name} as $xmlElement) {
			$elements[] = new self($xmlElement);
		}

		return $elements;
	}

	public function hasChild(string $name): bool
	{
		return isset($this->xmlElement->{$name});
	}

	public function hasChildren(): bool
	{
		foreach ($this->xmlElement as $key => $value) {
			return true;
		}

		return false;
	}

	public function getValue(): string
	{
		return (string) $this->xmlElement;
	}

	public function getAttribute(string $name): string
	{
		if (!$this->hasAttribute($name)) {
			throw new UnexpectedXmlStructureException(sprintf(
				'Attribute "%s" does not exists in <%s> element.',
				$name,
				$this->getName()
			));
		}

		return (string) $this->xmlElement[$name];
	}

	/**
	 * @return string[] [<attribute name> => <attribute value>, ...]
	 */
	public function getAttributes(): array
	{
		$attributes = [];
		/**
		 * @var string $name
		 * @var \SimpleXMLElement $xmlElement
		 */
		foreach ($this->xmlElement->attributes() as $name => $xmlElement) {
			$attributes[$name] = (string) $xmlElement;
		}

		return $attributes;
	}

	public function hasAttribute(string $name): bool
	{
		return isset($this->xmlElement[$name]);
	}

	public function hasAttributes(): bool
	{
		return (bool) $this->getAttributes();
	}

	private function getName(): string
	{
		return $this->xmlElement->getName();
	}
}
