<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Default Search Engine
    |--------------------------------------------------------------------------
    |
    | This option controls the default search connection that gets used while
    | using Laravel Scout. This connection is used when syncing all models
    | to the search service. You should adjust this based on your needs.
    |
    | Supported: "algolia", "meilisearch", "typesense",
    |            "database", "collection", "null"
    |
    */

    'driver' => env('SCOUT_DRIVER', 'algolia'),

    /*
    |--------------------------------------------------------------------------
    | Index Prefix
    |--------------------------------------------------------------------------
    |
    | Here you may specify a prefix that will be applied to all search index
    | names used by Scout. This prefix may be useful if you have multiple
    | "tenants" or applications sharing the same search infrastructure.
    |
    */

    'prefix' => env('SCOUT_PREFIX', ''),

    /*
    |--------------------------------------------------------------------------
    | Queue Data Syncing
    |--------------------------------------------------------------------------
    |
    | This option allows you to control if the operations that sync your data
    | with your search engines are queued. When this is set to "true" then
    | all automatic data syncing will get queued for better performance.
    |
    */

    'queue' => [
        'connection' => 'database',
        'queue' => 'scout',
    ],

    /*
    |--------------------------------------------------------------------------
    | Database Transactions
    |--------------------------------------------------------------------------
    |
    | This configuration option determines if your data will only be synced
    | with your search indexes after every open database transaction has
    | been committed, thus preventing any discarded data from syncing.
    |
    */

    'after_commit' => false,

    /*
    |--------------------------------------------------------------------------
    | Chunk Sizes
    |--------------------------------------------------------------------------
    |
    | These options allow you to control the maximum chunk size when you are
    | mass importing data into the search engine. This allows you to fine
    | tune each of these chunk sizes based on the power of the servers.
    |
    */

    'chunk' => [
        'searchable' => 500,
        'unsearchable' => 500,
    ],

    /*
    |--------------------------------------------------------------------------
    | Soft Deletes
    |--------------------------------------------------------------------------
    |
    | This option allows to control whether to keep soft deleted records in
    | the search indexes. Maintaining soft deleted records can be useful
    | if your application still needs to search for the records later.
    |
    */

    'soft_delete' => false,

    /*
    |--------------------------------------------------------------------------
    | Identify User
    |--------------------------------------------------------------------------
    |
    | This option allows you to control whether to notify the search engine
    | of the user performing the search. This is sometimes useful if the
    | engine supports any analytics based on this application's users.
    |
    | Supported engines: "algolia"
    |
    */

    'identify' => env('SCOUT_IDENTIFY', false),

    /*
    |--------------------------------------------------------------------------
    | Algolia Configuration
    |--------------------------------------------------------------------------
    |
    | Here you may configure your Algolia settings. Algolia is a cloud hosted
    | search engine which works great with Scout out of the box. Just plug
    | in your application ID and admin API key to get started searching.
    |
    */

    'algolia' => [
        'id' => env('ALGOLIA_APP_ID', ''),
        'secret' => env('ALGOLIA_SECRET', ''),
        'index-settings' => [
            // 'users' => [
            //     'searchableAttributes' => ['id', 'name', 'email'],
            //     'attributesForFaceting'=> ['filterOnly(email)'],
            // ],
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Meilisearch Configuration
    |--------------------------------------------------------------------------
    |
    | Here you may configure your Meilisearch settings. Meilisearch is an open
    | source search engine with minimal configuration. Below, you can state
    | the host and key information for your own Meilisearch installation.
    |
    | See: https://www.meilisearch.com/docs/learn/configuration/instance_options#all-instance-options
    |
    */

    'meilisearch' => [
        'host' => env('MEILISEARCH_HOST', 'http://localhost:7700'),
        'key' => env('MEILISEARCH_KEY'),
        'index-settings' => [
            // 'users' => [
            //     'filterableAttributes'=> ['id', 'name', 'email'],
            // ],
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Typesense Configuration
    |--------------------------------------------------------------------------
    |
    | Here you may configure your Typesense settings. Typesense is an open
    | source search engine using minimal configuration. Below, you will
    | state the host, key, and schema configuration for the instance.
    |
    */

    'typesense' => [
        'client-settings' => [
            'api_key' => env('TYPESENSE_API_KEY', 'xyz'),
            'nodes' => [
                [
                    'host' => env('TYPESENSE_HOST', 'localhost'),
                    'port' => env('TYPESENSE_PORT', '8108'),
                    'path' => env('TYPESENSE_PATH', ''),
                    'protocol' => env('TYPESENSE_PROTOCOL', 'http'),
                ],
            ],
            'nearest_node' => [
                'host' => env('TYPESENSE_HOST', 'localhost'),
                'port' => env('TYPESENSE_PORT', '8108'),
                'path' => env('TYPESENSE_PATH', ''),
                'protocol' => env('TYPESENSE_PROTOCOL', 'http'),
            ],
            'connection_timeout_seconds' => env('TYPESENSE_CONNECTION_TIMEOUT_SECONDS', 2),
            'healthcheck_interval_seconds' => env('TYPESENSE_HEALTHCHECK_INTERVAL_SECONDS', 30),
            'num_retries' => env('TYPESENSE_NUM_RETRIES', 3),
            'retry_interval_seconds' => env('TYPESENSE_RETRY_INTERVAL_SECONDS', 1),
        ],
        // 'max_total_results' => env('TYPESENSE_MAX_TOTAL_RESULTS', 1000),
        'model-settings' => [
            // User::class => [
            //     'collection-schema' => [
            //         'fields' => [
            //             [
            //                 'name' => 'id',
            //                 'type' => 'string',
            //             ],
            //             [
            //                 'name' => 'name',
            //                 'type' => 'string',
            //             ],
            //             [
            //                 'name' => 'created_at',
            //                 'type' => 'int64',
            //             ],
            //         ],
            //         'default_sorting_field' => 'created_at',
            //     ],
            //     'search-parameters' => [
            //         'query_by' => 'name'
            //     ],
            // ],
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | TNTSearch Configuration
    |--------------------------------------------------------------------------
    */

    'tntsearch' => [
        'storage' => storage_path() . "/scout", // place where the index files will be stored
        'fuzziness' => env('TNTSEARCH_FUZZINESS', true),
        'fuzzy' => [
            'prefix_length' => 2,
            'max_expansions' => 50,
            'distance' => 2,
            'no_limit' => true,
        ],
        'asYouType' => false,
        'searchBoolean' => env('TNTSEARCH_BOOLEAN', false),
        'maxDocs' => env('TNTSEARCH_MAX_DOCS', 500),
        // 'stemmer' => \TeamTNT\TNTSearch\Stemmer\PorterStemmer::class,
        'stopwords' => array_merge([
            'a', 'à', 'afin', 'ai', 'aie', 'aient', 'aies', 'ait', 'alors', 'après', 'as', 'au', 'aucun', 'aucune', 'aura', 'aurai', 'auraient', 'aurais', 'aurait', 'auras', 'aurez', 'auriez', 'aurions', 'aurons', 'auront', 'aussi', 'autre', 'autres', 'aux', 'avaient', 'avais', 'avait', 'avant', 'avec', 'avez', 'aviez', 'avions', 'avoir', 'avons', 'ayant', 'ayez', 'ayons', 'bon', 'car', 'ce', 'ceci', 'cela', 'celà', 'ces', 'cet', 'cette', 'ceux', 'ceux-ci', 'ceux-là', 'chacun', 'chaque', 'chez', 'ci', 'comme', 'comment', 'd', 'dans', 'de', 'dedans', 'dehors', 'delà', 'depuis', 'des', 'deux', 'devrait', 'doit', 'doivent', 'donc', 'dont', 'dos', 'droite', 'du', 'dès', 'début', 'dù', 'elle', 'elles', 'en', 'encore', 'entre', 'envers', 'environ', 'es', 'est', 'et', 'etaient', 'etais', 'etait', 'etant', 'etc', 'ete', 'etes', 'etiez', 'etions', 'eu', 'eue', 'eues', 'eurent', 'eus', 'eusse', 'eussent', 'eusses', 'eussiez', 'eussions', 'eut', 'eux', 'eût', 'eûmes', 'eûtes', 'faire', 'fais', 'faisaient', 'faisais', 'faisait', 'faisant', 'fait', 'faites', 'faits', 'feront', 'fi', 'flac', 'floc', 'font', 'force', 'gens', 'h', 'ha', 'haut', 'hein', 'hé', 'hem', 'hep', 'hi', 'ho', 'holà', 'hop', 'hormis', 'hors', 'hui', 'huit', 'hum', 'hurrah', 'i', 'ici', 'il', 'ils', 'j', 'je', 'jusqu', 'jusque', 'l', 'la', 'laquelle', 'las', 'le', 'lequel', 'les', 'lesquelles', 'lesquels', 'leur', 'leurs', 'lès', 'loin', 'longtemps', 'lors', 'lorsque', 'lui', 'là', 'm', 'ma', 'maintenant', 'mais', 'malgré', 'me', 'meme', 'mêmes', 'merci', 'mes', 'mien', 'mienne', 'miennes', 'miens', 'moi', 'moi-même', 'moins', 'mon', 'mot', 'même', 'n', 'ne', 'neuf', 'ni', 'nombreuses', 'nombreux', 'nommés', 'non', 'nos', 'notre', 'nous', 'nous-mêmes', 'nouveau', 'nouveaux', 'nul', 'néanmoins', 'o', 'oh', 'on', 'ont', 'ou', 'où', 'p', 'par', 'parce', 'parfois', 'parle', 'parlent', 'parler', 'parmi', 'partout', 'pas', 'passé', 'pendant', 'personne', 'peu', 'peut', 'peuvent', 'peux', 'pick', 'pire', 'pièce', 'plein', 'plus', 'plusieurs', 'plutôt', 'pour', 'pourquoi', 'pourraient', 'pourrais', 'pourrait', 'pourras', 'pourrez', 'pourriez', 'pourrions', 'pourrons', 'pourront', 'pouvait', 'pouvaient', 'pres', 'presque', 'près', 'pu', 'puis', 'puisque', 'q', 'qu', 'quand', 'quant', 'quanta', 'quant-à-soi', 'quarante', 'quatorze', 'quatre', 'quatre-vingt', 'quatre-vingt-dix', 'quatre-vingts', 'que', 'quel', 'quelle', 'quelles', 'quelqu\'un', 'quelque', 'quelques', 'quels', 'qui', 'quiconque', 'quinze', 'quoi', 'quoique', 'r', 'revoici', 'revoilà', 'rien', 's', 'sa', 'sacrebleu', 'sans', 'sapristi', 'sauf', 'se', 'seize', 'selon', 'sept', 'sera', 'serai', 'seraient', 'serais', 'serait', 'seras', 'serez', 'seriez', 'serions', 'serons', 'seront', 'ses', 'seulement', 'si', 'sien', 'sienne', 'siennes', 'siens', 'sinon', 'six', 'soi', 'soi-même', 'soient', 'sois', 'soit', 'soixante', 'sommes', 'son', 'sont', 'sous', 'souvent', 'soyez', 'soyons', 'stop', 'suis', 'sur', 'sûr', 't', 'ta', 'tac', 'tandis', 'tant', 'te', 'tel', 'telle', 'tellement', 'telles', 'tels', 'tenant', 'tes', 'tic', 'tien', 'tienne', 'tiennes', 'tiens', 'toi', 'toi-même', 'ton', 'toujours', 'tous', 'tout', 'toute', 'toutes', 'treize', 'trente', 'très', 'trois', 'trop', 'tu', 'té', 'u', 'un', 'une', 'unes', 'uns', 'v', 'va', 'vais', 'vas', 'vers', 'via', 'vif', 'vifs', 'vingt', 'vivat', 'vive', 'vives', 'voici', 'voilà', 'vos', 'votre', 'vous', 'vous-mêmes', 'vu', 'vé', 'vôtre', 'vôtres', 'w', 'x', 'y', 'z', 'zut', 'ça', 'ès', 'étaient', 'étais', 'était', 'étant', 'été', 'étiez', 'étions', 'êtes', 'être', 'ô',
        ], [
            'a', 'about', 'above', 'after', 'again', 'against', 'all', 'am', 'an', 'and', 'any', 'are', 'aren\'t', 'as', 'at', 'be', 'because', 'been', 'before', 'being', 'below', 'between', 'both', 'but', 'by', 'can\'t', 'cannot', 'could', 'couldn\'t', 'did', 'didn\'t', 'do', 'does', 'doesn\'t', 'doing', 'don\'t', 'down', 'during', 'each', 'few', 'for', 'from', 'further', 'had', 'hadn\'t', 'has', 'hasn\'t', 'have', 'haven\'t', 'having', 'he', 'he\'d', 'he\'ll', 'he\'s', 'her', 'here', 'here\'s', 'hers', 'herself', 'him', 'himself', 'his', 'how', 'how\'s', 'i', 'i\'d', 'i\'ll', 'i\'m', 'i\'ve', 'if', 'in', 'into', 'is', 'isn\'t', 'it', 'it\'s', 'its', 'itself', 'let\'s', 'me', 'more', 'most', 'mustn\'t', 'my', 'myself', 'no', 'nor', 'not', 'of', 'off', 'on', 'once', 'only', 'or', 'other', 'ought', 'our', 'ours', 'ourselves', 'out', 'over', 'own', 'same', 'shan\'t', 'she', 'she\'d', 'she\'ll', 'she\'s', 'should', 'shouldn\'t', 'so', 'some', 'such', 'than', 'that', 'that\'s', 'the', 'their', 'theirs', 'them', 'themselves', 'then', 'there', 'there\'s', 'these', 'they', 'they\'d', 'they\'ll', 'they\'re', 'they\'ve', 'this', 'those', 'through', 'to', 'too', 'under', 'until', 'up', 'very', 'was', 'wasn\'t', 'we', 'we\'d', 'we\'ll', 'we\'re', 'we\'ve', 'were', 'weren\'t', 'what', 'what\'s', 'when', 'when\'s', 'where', 'where\'s', 'which', 'while', 'who', 'who\'s', 'whom', 'why', 'why\'s', 'with', 'won\'t', 'would', 'wouldn\'t', 'you', 'you\'d', 'you\'ll', 'you\'re', 'you\'ve', 'your', 'yours', 'yourself', 'yourselves',
        ])
    ],
];
