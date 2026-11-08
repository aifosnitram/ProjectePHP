<?php
    namespace models;

    class Country extends Model{
        protected static string $table = 'countries';

        // Constructor: l'ordre ha de coincidir amb les columnes (state_code, state_name)
        public function __construct(
            public ?string $state_code = null,
            public ?string $state_name = null
        ){ }

        // Taula de consulta: l'aplicació només la llegeix
        public function save(){
            throw new \RuntimeException('La taula countries és només de consulta.');
        }

        public function destroy(){
            throw new \RuntimeException('La taula countries és només de consulta.');
        }

        // Per als desplegables: state_code => state_name
        public static function options(): array {
            $options = [];
            foreach (self::all() as $country) {
                $options[$country->state_code] = $country->state_name;
            }
            return $options;
        }
    }