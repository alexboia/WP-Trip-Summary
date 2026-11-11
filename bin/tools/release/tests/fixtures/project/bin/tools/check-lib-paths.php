<?php
exit(getenv('WPTS_RELEASE_TEST_FAILURE') === 'library' ? 1 : 0);
