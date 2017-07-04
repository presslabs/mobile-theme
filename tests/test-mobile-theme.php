<?php

use function Patchwork\always;
use function Patchwork\redefine;
use function Patchwork\restoreAll;


class MobileThemeTest extends WP_UnitTestCase {
	public function tearDown() {
		restoreAll();
		parent::tearDown();
	}

	public function test_sample() {
     $this->asssertTrue(true);
  }
}
