<?php

declare(strict_types=1);

class TestDriver_Session extends Application_Session_Native implements Application_Session_AuthTypes_NoneInterface
{
    use Application_Session_AuthTypes_None;
}
