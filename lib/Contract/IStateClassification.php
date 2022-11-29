<?php

namespace OCA\Files_Confidential\Contract;

interface IStateClassification {
	public const RESTRICTED = 1;
	public const CONFIDENTIAL = 2;
	public const SECRET = 4;
	public const TOP_SECRET = 8;
}
