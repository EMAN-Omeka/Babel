<?php
  
class TranslatedMetadata extends Omeka_View_Helper_Metadata 
{
    /**
     * Retrieve a specific piece of a record's metadata for display.
     *
     * @param Omeka_Record_AbstractRecord $record Database record representing 
     * the item from which to retrieve field data.
     * @param string|array $metadata The metadata field to retrieve.
     *  If a string, refers to a property of the record itself.
     *  If an array, refers to an Element: the first entry is the set name,
     *  the second is the element name.
     * @param array|string|integer $options Options for formatting the metadata
     * for display.
     * - Array options:
     *   - 'all': If true, return an array containing all values for the field.
     *   - 'delimiter': Return the entire set of metadata as a string, where
     *     entries are separated by the given delimiter.
     *   - 'index': Return the metadata entry at the given zero-based index.
     *   - 'no_escape' => If true, do not escape the resulting values for HTML
     *     entities.
     *   - 'no_filter': If true, return the set of metadata without
     *     running any of the filters.
     *   - 'snippet': Trim the length of each piece of text to the given
     *     length in characters.
     * - Passing simply the string 'all' is equivalent to array('all' => true)
     * - Passing simply an integer is equivalent to array('index' => [the integer])
     * @return string|array|null Null if field does not exist for item. Array
     * if certain options are passed.  String otherwise.
     */
    public function metadata($record, $metadata, $options = array())
    {
        if (is_string($record)) {
            $record = $this->view->getCurrentRecord($record);
        }

        if (!($record instanceof Omeka_Record_AbstractRecord)) {
            throw new InvalidArgumentException('Invalid record passed to recordMetadata.');
        }

        // Convert the shortcuts for the options into a proper array.
        $options = $this->_getOptions($options);

        $snippet = isset($options[self::SNIPPET]) ? (int) $options[self::SNIPPET] : false;
        $escape = empty($options[self::NO_ESCAPE]);
        $filter = empty($options[self::NO_FILTER]);
        $all = isset($options[self::ALL]) && $options[self::ALL];
        $delimiter = isset($options[self::DELIMITER]) ? (string) $options[self::DELIMITER] : false;
        $index = isset($options[self::INDEX]) ? (int) $options[self::INDEX] : 0;
        $ignoreUnknown = isset($options[self::IGNORE_UNKNOWN]) && $options[self::IGNORE_UNKNOWN];
        try {
            $text = $this->_getText($record, $metadata);
        } catch (Omeka_Record_Exception $e) {
            if ($ignoreUnknown) {
                $text = null;
            } else {
                throw $e;
            }
        }

        if (is_array($text)) {
            // If $all or $delimiter isn't specified, pare the array down to
            // just one entry, otherwise we need to work on the whole thing
            if ($all || $delimiter) {
                foreach ($text as $key => $value) {
                    $text[$key] = $this->_process(
                        $record, $metadata, $value, $snippet, $escape, $filter);
                }

                // Return the joined text if there was a delimiter
                if ($delimiter) {
                    return join($delimiter, $text);
                } else {
                    return $text;
                }
            } else {
                // Return null if the index doesn't exist for the record.
                if (!isset($text[$index])) {
                    $text = null;
                } else {
                    $text = $text[$index];
                }
            }
        }

        // If we get here, we're working with a single value only.
        return $this->_process($record, $metadata, $text, $snippet, $escape, $filter);
    }  
    public function TranslationHelper() {
      
    }
  
}