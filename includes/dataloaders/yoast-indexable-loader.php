<?php

namespace WP_Graphql_YOAST_SEO\Data\Loader;

use WPGraphQL\Data\Loader\AbstractDataLoader;

/**
 * Class YoastIndexableLoader
 *
 * @package WP_Graphql_YOAST_SEO\Data\Loader
 */
class YoastIndexableLoader extends AbstractDataLoader
{
    /**
     * @var string
     */
    protected $type;

    /**
     * YoastIndexableLoader constructor.
     *
     * @param \WPGraphQL\AppContext $context The AppContext instance.
     * @param string $type The type of indexable to load ('post', 'term', 'user').
     */
    public function __construct($context, string $type = 'post')
    {
        parent::__construct($context);
        $this->type = $type;
    }

    /**
     * @param mixed $entry The loaded entry.
     * @param mixed $key   The Key to identify the entry by.
     *
     * @return mixed
     */
    protected function get_model($entry, $key)
    {
        return $entry;
    }

    /**
     * Given array of keys, loads and returns a map consisting of keys from `keys` array and loaded values
     *
     * @param int[]|string[] $keys Array of IDs to load
     *
     * @return array<int|string,mixed>
     */
    public function loadKeys(array $keys)
    {
        if (empty($keys)) {
            return $keys;
        }

        // Sanitize and validate keys to ensure they are integers (post, term, user IDs)
        $valid_keys = [];
        foreach ($keys as $key) {
            if (is_numeric($key) && $key > 0) {
                $valid_keys[] = (int) $key;
            }
        }
        
        $loaded = [];
        
        if (empty($valid_keys)) {
            foreach ($keys as $key) {
                $loaded[$key] = false;
            }
            return $loaded;
        }
        
        $valid_keys_lookup = array_fill_keys($valid_keys, true);
        
        try {
            if ($this->type === 'post') {
                $metas = YoastSEO()->meta->for_posts($valid_keys);
                
                // for_posts returns an array keyed by post ID
                foreach ($keys as $key) {
                    $loaded[$key] = isset($valid_keys_lookup[(int)$key]) && isset($metas[$key]) ? $metas[$key] : false;
                }
            } else if ($this->type === 'term') {
                // Yoast doesn't have a batch API for terms yet, so we have to loop
                // but at least we're doing it in a loader pattern for future-proofing
                foreach ($keys as $key) {
                    if (isset($valid_keys_lookup[(int)$key])) {
                        $loaded[$key] = YoastSEO()->meta->for_term((int)$key);
                    } else {
                        $loaded[$key] = false;
                    }
                }
            } else if ($this->type === 'user') {
                // Yoast doesn't have a batch API for authors yet
                foreach ($keys as $key) {
                    if (isset($valid_keys_lookup[(int)$key])) {
                        $loaded[$key] = YoastSEO()->meta->for_author((int)$key);
                    } else {
                        $loaded[$key] = false;
                    }
                }
            } else {
                foreach ($keys as $key) {
                    $loaded[$key] = false;
                }
            }
        } catch (\Exception $e) {
            // Log error if needed, but fail gracefully for GraphQL response.
            // Only log when WordPress debugging is enabled to avoid spamming production logs.
            if (
                function_exists('error_log')
                && (
                    ( defined( 'WP_DEBUG' ) && WP_DEBUG )
                    || ( defined( 'WP_DEBUG_LOG' ) && WP_DEBUG_LOG )
                )
            ) {
                error_log( 'YoastIndexableLoader Error: ' . $e->getMessage() );
            }
            foreach ($keys as $key) {
                $loaded[$key] = false;
            }
        }

        return $loaded;
    }
}
