<?php
/**
 * Babel
 *
 * @copyright Copyright 2017 by Numerizen
 * @license http://www.gnu.org/licenses/gpl-3.0.txt GNU GPLv3
 */

/**
 * The Babel translation record class.
 *
 * @package SimplePages
 */
class TranslationRecord extends Omeka_Record_AbstractRecord {
    public $record_id;
    public $record_type;
    public $element_set;
    public $element_id;
    public $element_number;
    public $lang;
    public $text;
    public $html = 0;
}