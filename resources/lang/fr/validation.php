<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Validation Language Lines
    |--------------------------------------------------------------------------
    |
    | The following language lines contain the default error messages used by
    | the validator class. Some of these rules have multiple versions such
    | as the size rules. Feel free to tweak each of these messages here.
    |
    */

    'accepted'             => 'L\'attribut: doit être accepté.',
    'active_url'           => 'L\'attribut: n\'est pas une URL valide.',
    'after'                => 'L`\'attribut: doit être une date après: date.',
    'after_or_equal'       => 'L\'attribut: doit être une date après ou égale à: date.',
    'alpha'                => 'L\'attribut: ne peut contenir que des lettres.',
    'alpha_dash'           => 'L\'attribut: ne peut contenir que des lettres, des chiffres et des tirets.',
    'alpha_num'            => 'L\'attribut: ne peut contenir que des lettres et des chiffres.',
    'array'                => 'L\'attribut: doit être un tableau.',
    'before'               => 'L\'attribut: doit être une date antérieure à: date.',
    'before_or_equal'      => 'L\'attribut: doit être une date antérieure ou égale à: date.',
    'between'              => [
        'numeric' => 'L\'attribut: doit être compris entre: min et: max.',
        'file'    => 'L\'attribut: doit être compris entre: min et: max kilo-octets.',
        'string'  => 'L\'attribut: doit être compris entre: min et: max caractères.',
        'array'   => 'L\'attribut: doit avoir entre: min et: max caractères.',
    ],
    'boolean'              => 'Le champ d\'attribut: doit être vrai ou faux.',
    'confirmed'            => 'La confirmation d\'attribut ne correspond pas.',
    'date'                 => 'L\'attribut: n\'est pas une date valide.',
    'date_format'          => 'L\'attribut: ne correspond pas au format: format.',
    'different'            => 'L\'attribut: et: autre doivent être différents.',
    'digits'               => 'L\'attribut: doit être: digits digits.',
    'digits_between'       => 'L\'attribut: doit être compris entre: min et: max digits.',
    'dimensions'           => 'L\'attribut: a des dimensions d\'image non valides.',
    'distinct'             => 'Le champ d\'attribut: a une valeur en double.',
    'email'                => 'L\'attribut: doit être une adresse email valide.',
    'exists'               => 'L\'attribut sélectionné: n\'est pas valide.',
    'file'                 => 'L\'attribut: doit être un fichier.',
    'filled'               => 'Le champ d\'attribut: est requis.',
    'image'                => 'L\'attribut: doit être une image.',
    'in'                   => 'L\'attribut sélectionné: n\'est pas valide.',
    'in_array'             => 'Le champ: attribut n\'existe pas dans: autre.',
    'integer'              => 'L\'attribut: doit être un entier.',
    'ip'                   => 'L\'attribut: doit être une adresse IP valide.',
    'json'                 => 'L\'attribut: doit être une chaîne JSON valide.',
    'max'                  => [
        'numeric' => 'L\'attribut: ne peut pas être supérieur à: max.',
        'file'    => 'L\'attribut: ne peut pas être supérieur à: max kilo-octets.',
        'string'  => 'L\'attribut: ne peut pas être supérieur à: max caractères.',
        'array'   => 'L\'attribut: ne peut avoir plus de: max articles.',
    ],
    'mimes'                => 'L\'attribut: doit être un fichier de type:: valeurs.',
    'mimetypes'            => 'L\'attribut: doit être un fichier de type:: valeurs.',
    'min'                  => [
        'numeric' => 'L\'attribut: doit être au moins: min.',
        'file'    => 'L\'attribut: doit être au moins: min kilo-octets.',
        'string'  => 'L\'attribut: doit être au moins: min caractères.',
        'array'   => 'L\'attribut: doit avoir au moins: min items.',
    ],
    'not_in'               => 'L\'attribut sélectionné: n\'est pas valide.',
    'numeric'              => 'L\'attribut: doit être un nombre.',
    'present'              => 'Le champ d\'attribut: doit être présent.',
    'regex'                => 'Le format d\'attribut est invalide.',
    'required'             => 'Le champ d\'attribut: est requis.',
    'required_if'          => 'Le champ d\'attribut: est requis lorsque: autre est: valeur.',
    'required_unless'      => 'Le champ: attribut est obligatoire sauf si: autre est dans: valeurs.',
    'required_with'        => 'Le champ: attribut est requis lorsque: values ​​est présent.',
    'required_with_all'    => 'Le champ: attribut est requis lorsque: values ​​est présent.',
    'required_without'     => 'Le champ: attribut est requis lorsque: values ​​n\'est pas présent.',
    'required_without_all' => 'Le champ d\'attribut: est requis lorsqu\'aucune des valeurs suivantes n\'est présente.',
    'same'                 => 'L\'attribut: et: other doivent correspondre.',
    'size'                 => [
        'numeric' => 'L\'attribut: doit être: taille.',
        'file'    => 'L\'attribut: doit être: taille kilo-octets.',
        'string'  => 'L\'attribut: doit être: caractères de taille.',
        'array'   => 'L\'attribut: doit contenir: les éléments de taille.',
    ],
    'string'               => 'L\'attribut: doit être une chaîne.',
    'timezone'             => 'L\'attribut: doit être une zone valide.',
    'unique'               => 'L\'attribut: a déjà été pris.',
    'uploaded'             => 'L\'attribut: n\'a pas pu être téléchargé.',
    'url'                  => 'Le format d\'attribut est invalide.',

    /*
    |--------------------------------------------------------------------------
    | Custom Validation Language Lines
    |--------------------------------------------------------------------------
    |
    | Here you may specify custom validation messages for attributes using the
    | convention "attribute.rule" to name the lines. This makes it quick to
    | specify a specific custom language line for a given attribute rule.
    |
    */

    'custom' => [
        's_latitude' => [
            'required' => 'Adresse source requise',
        ],
        'd_latitude' => [
            'required' => 'Adresse de destination requise',
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Custom Validation Attributes
    |--------------------------------------------------------------------------
    |
    | The following language lines are used to swap attribute place-holders
    | with something more reader friendly such as E-Mail Address instead
    | of "email". This simply helps us make messages a little cleaner.
    |
    */

    'attributes' => [],

];
