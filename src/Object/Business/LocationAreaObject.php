<?php

namespace Api\Object\Business;

class LocationAreaObject
{
	public function __construct(
		public readonly string $id,
		public readonly string $name,
	) {}
}
