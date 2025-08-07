<?php

    class limePDF_WebColors
    {
        /**
         * Get all predefined web colors.
         *
         * @return array<string, array{int, int, int}>
         */
        public static function getColors(): array
        {
            return [
                'black'     => [0, 0, 0],
                'white'     => [255, 255, 255],
                'red'       => [255, 0, 0],
                'lime'      => [0, 255, 0],
                'blue'      => [0, 0, 255],
                'yellow'    => [255, 255, 0],
                'cyan'      => [0, 255, 255],
                'magenta'   => [255, 0, 255],
                'silver'    => [192, 192, 192],
                'gray'      => [128, 128, 128],
                'maroon'    => [128, 0, 0],
                'olive'     => [128, 128, 0],
                'green'     => [0, 128, 0],
                'purple'    => [128, 0, 128],
                'teal'      => [0, 128, 128],
                'navy'      => [0, 0, 128],
                // Add more as needed...
            ];
        }

        /**
         * Get an RGB array for a specific color name.
         *
         * @param string $name
         * @return array{int, int, int}|null
         */
        public static function getColor(string $name): ?array
        {
            return self::getColors()[strtolower($name)] ?? null;
        }

        /**
         * Get a hexadecimal color string for a specific color name.
         *
         * @param string $name
         * @return string|null
         */
        public static function getHex(string $name): ?string
        {
            $rgb = self::getColor($name);
            return $rgb ? sprintf("#%02x%02x%02x", ...$rgb) : null;
        }

        /**
         * Reverse-lookup an RGB value to a color name.
         *
         * @param array{int, int, int} $rgb
         * @return string|null
         */
        public static function getName(array $rgb): ?string
        {
            foreach (self::getColors() as $name => $color) {
                if ($color === $rgb) {
                    return $name;
                }
            }
            return null;
        }
    }
