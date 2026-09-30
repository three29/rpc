<?php

namespace RPC;

/**
 * Exists only so I can do a <code>new DataObject</code> instead of
 * <code>new stdclass</code> when I need an empty object
 *
 * @package Core
 */
#[\AllowDynamicProperties]
class DataObject
{
}

?>
