<?php

namespace Api\Object;

class HostObject
{
	public function __construct(
		public readonly string $name,
		public readonly string $picture,
	) {}
}
