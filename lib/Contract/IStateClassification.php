<?php

namespace OCA\Files_Confidential\Contract;

interface IStateClassification
{
	const RESTRICTED = 1;
	const CONFIDENTIAL = 2;
	const SECRET = 4;
	const TOP_SECRET = 8;

}
