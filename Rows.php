<?php
namespace SermoDigital\ACF_Util;

/**
 * Rows is a singleton Iterator that encapsulates ACF's `have_rows` and `the_row`.
 *
 * It turns this:
 *
 *     if (have_rows($field, $post_id)) {
 *         while (have_rows($field, $post_id)) {
 *             the_row();
 *
 *             ... = get_sub_field( ... )
 *         }
 *     }
 *
 * into
 *
 *     use SermoDigital\ACF_Util\Rows as Rows;
 *
 *     $inst = Rows::instance($field, $post_id);
 *     foreach ($inst as $i => $elem) {
 *        ... = $inst->get_sub_field( ... ); // or $elem[ ... ]
 *     }
 *
 */
class Rows implements \Iterator {
    /**
     * @var string
     */
    private $field_name;

    /**
     * @var string
     */
    private $post_id;

    /**
     * @var int
     */
    private $i = 0;

    /**
     * @var bool
     */
    public $format = true;
    
    /**
     * Rows constructor.
     *
     * @param string      $field_name Name of 'repeater' or 'flexible content
     *                                field' to loop through.
     * @param string|null $post_id    Specific post ID, defaulting to current
     *                                post ID. (May also be 'options',
     *                                'taxonomies', etc.)
     * @param bool         $format    Whether to format values loaded from the
     *                                database. (Defaults to true.)
     *
     * @return Rows
     */
    final public static function instance(string $field_name, ?string $post_id = null, $format = true): Rows {
        static $inst = null;
        if ($inst === null) {
            $inst = new static($field_name, $post_id, $format);
        } else {
            $inst->init($field_name, $post_id, $format);
            $inst->rewind();
        }
        return $inst;
    }

    private function __construct(string $field_name, ?string $post_id, $format = true) {
        $this->init($field_name, $post_id, $format);
    }

    private function init(string $field_name, ?string $post_id, $format = true) {
        $this->field_name = $field_name;
        $this->post_id    = $post_id;
        $this->format     = $format;
    }

    private function __clone() {}
    private function __sleep() {}
    private function __wakeup() {}

    /**
     * Retrieve the sub field value.
     *
     * @param string $field  Field name to be retrieved.
     * @param bool   $format Whether the value loaded from the DB should be
     *                       formatted.
     * 
     * @return mixed
     */
    public static function get_sub_field(string $field, bool $format = true) {
      return get_sub_field($field, $format);
    }

    /**
     * Return the current row.
     *
     * @return array
     */
    public function current() { return the_row($this->format); }

    /**
     * Return the index of the iteration.
     *
     * @return int
     */
    public function key() { return $this->i; }

    /**
     * Advance forward one element.
     */
    public function next() { ++$this->i; }

    /**
     * Rewind the Iterator
     */
    public function rewind()  {
        reset_rows(true);
        have_rows($this->field_name, $this->post_id);
    }

    /**
     * Return whether there are more rows to return.
     *
     * @return bool
     */
    public function valid(): bool {
        return have_rows($this->field_name, $this->post_id);
    }
}
?>
