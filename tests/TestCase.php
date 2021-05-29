<?php

namespace Kraber\Test;

use ReflectionClass;

class TestCase extends \PHPUnit\Framework\TestCase
{
	public function getPropertyValue(object|string $class, string $property) : mixed {
		$reflectionClass = new ReflectionClass($class);
		$prop = $reflectionClass->getProperty($property);
		if ($prop->isProtected() || $prop->isPrivate()) {
			$prop->setAccessible(true);
		}
		
		$value = $prop->getValue($prop->isStatic() ? null : $class);
		
		if ($prop->isProtected() || $prop->isPrivate()) {
			$prop->setAccessible(false);
		}
		
		return $value;
	}
	
	public function setPropertyValue(object|string $class, string $property, mixed $value) : void {
		$reflectionClass = new ReflectionClass($class);
		$prop = $reflectionClass->getProperty($property);
		if ($prop->isProtected() || $prop->isPrivate()) {
			$prop->setAccessible(true);
		}
		
		$prop->setValue($prop->isStatic() ? null : $class, $value);
		
		if ($prop->isProtected() || $prop->isPrivate()) {
			$prop->setAccessible(false);
		}
	}
}
