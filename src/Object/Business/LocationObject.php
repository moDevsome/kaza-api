<?php

namespace Api\Object\Business;

class LocationObject
{
	public function __construct(
		public readonly string $id,
		public readonly string $name,
		public readonly LocationAreaObject $area,
	) {}
}
