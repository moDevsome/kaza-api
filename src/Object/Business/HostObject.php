<?php

namespace Api\Object\Business;

class HostObject
{
	public function __construct(
		public readonly string $name,
		public readonly string $picture,
	) {}
}
