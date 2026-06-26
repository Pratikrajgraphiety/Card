<?php

namespace App\Services;

final class QrCodeService
{
    private int $version;
    private int $size;
    private array $modules = [];
    private array $isFunction = [];

    private const LEVEL_M_FORMAT_BITS = 0b00;

    private const VERSIONS = [
        1 => ['data' => 16, 'ec' => 10, 'blocks' => [16], 'align' => []],
        2 => ['data' => 28, 'ec' => 16, 'blocks' => [28], 'align' => [6, 18]],
        3 => ['data' => 44, 'ec' => 26, 'blocks' => [44], 'align' => [6, 22]],
        4 => ['data' => 64, 'ec' => 18, 'blocks' => [32, 32], 'align' => [6, 26]],
        5 => ['data' => 86, 'ec' => 24, 'blocks' => [43, 43], 'align' => [6, 30]],
        6 => ['data' => 108, 'ec' => 16, 'blocks' => [27, 27, 27, 27], 'align' => [6, 34]],
    ];

    public static function svg(string $text, int $scale = 8, int $border = 4): string
    {
        $qr = self::encode($text);
        return $qr->toSvg($scale, $border);
    }

    private static function encode(string $text): self
    {
        $bytes = array_values(unpack('C*', $text) ?: []);
        $version = self::selectVersion(count($bytes));
        $spec = self::VERSIONS[$version];

        $dataCodewords = self::encodeDataCodewords($bytes, $spec['data']);
        [$dataBlocks, $ecBlocks] = self::buildBlocks($dataCodewords, $spec['blocks'], $spec['ec']);
        $finalCodewords = self::interleave($dataBlocks, $ecBlocks);

        $qr = new self();
        $qr->version = $version;
        $qr->size = $version * 4 + 17;
        $qr->modules = array_fill(0, $qr->size, array_fill(0, $qr->size, false));
        $qr->isFunction = array_fill(0, $qr->size, array_fill(0, $qr->size, false));
        $qr->drawFunctionPatterns($spec['align']);
        $qr->drawCodewords($finalCodewords);
        $qr->applyBestMask();

        return $qr;
    }

    private static function selectVersion(int $byteLength): int
    {
        foreach (self::VERSIONS as $version => $spec) {
            if (4 + 8 + ($byteLength * 8) <= $spec['data'] * 8) {
                return $version;
            }
        }

        throw new \LengthException('QR payload is too long. Use a shorter APP_URL or username.');
    }

    private static function encodeDataCodewords(array $bytes, int $dataCodewords): array
    {
        $bits = [];
        self::appendBits($bits, 0b0100, 4);
        self::appendBits($bits, count($bytes), 8);
        foreach ($bytes as $byte) {
            self::appendBits($bits, $byte, 8);
        }

        $capacityBits = $dataCodewords * 8;
        $terminator = min(4, $capacityBits - count($bits));
        for ($i = 0; $i < $terminator; $i++) {
            $bits[] = 0;
        }

        while (count($bits) % 8 !== 0) {
            $bits[] = 0;
        }

        $codewords = [];
        for ($i = 0; $i < count($bits); $i += 8) {
            $value = 0;
            for ($j = 0; $j < 8; $j++) {
                $value = ($value << 1) | $bits[$i + $j];
            }
            $codewords[] = $value;
        }

        for ($pad = 0; count($codewords) < $dataCodewords; $pad++) {
            $codewords[] = ($pad % 2 === 0) ? 0xEC : 0x11;
        }

        return $codewords;
    }

    private static function appendBits(array &$bits, int $value, int $length): void
    {
        for ($i = $length - 1; $i >= 0; $i--) {
            $bits[] = ($value >> $i) & 1;
        }
    }

    private static function buildBlocks(array $dataCodewords, array $blockSizes, int $ecLength): array
    {
        $dataBlocks = [];
        $ecBlocks = [];
        $offset = 0;

        foreach ($blockSizes as $size) {
            $block = array_slice($dataCodewords, $offset, $size);
            $offset += $size;
            $dataBlocks[] = $block;
            $ecBlocks[] = self::reedSolomonRemainder($block, $ecLength);
        }

        return [$dataBlocks, $ecBlocks];
    }

    private static function interleave(array $dataBlocks, array $ecBlocks): array
    {
        $result = [];
        $maxData = max(array_map('count', $dataBlocks));

        for ($i = 0; $i < $maxData; $i++) {
            foreach ($dataBlocks as $block) {
                if (array_key_exists($i, $block)) {
                    $result[] = $block[$i];
                }
            }
        }

        $maxEc = max(array_map('count', $ecBlocks));
        for ($i = 0; $i < $maxEc; $i++) {
            foreach ($ecBlocks as $block) {
                if (array_key_exists($i, $block)) {
                    $result[] = $block[$i];
                }
            }
        }

        return $result;
    }

    private static function reedSolomonRemainder(array $data, int $degree): array
    {
        $generator = [1];
        for ($i = 0; $i < $degree; $i++) {
            $generator = self::polyMultiply($generator, [1, self::gfPow($i)]);
        }

        $remainder = array_fill(0, $degree, 0);
        foreach ($data as $byte) {
            $factor = $byte ^ $remainder[0];
            array_shift($remainder);
            $remainder[] = 0;

            for ($i = 0; $i < $degree; $i++) {
                $remainder[$i] ^= self::gfMultiply($generator[$i + 1], $factor);
            }
        }

        return $remainder;
    }

    private static function polyMultiply(array $a, array $b): array
    {
        $result = array_fill(0, count($a) + count($b) - 1, 0);
        foreach ($a as $i => $x) {
            foreach ($b as $j => $y) {
                $result[$i + $j] ^= self::gfMultiply($x, $y);
            }
        }
        return $result;
    }

    private static function gfMultiply(int $x, int $y): int
    {
        if ($x === 0 || $y === 0) {
            return 0;
        }
        [$exp, $log] = self::gfTables();
        return $exp[($log[$x] + $log[$y]) % 255];
    }

    private static function gfPow(int $power): int
    {
        [$exp] = self::gfTables();
        return $exp[$power % 255];
    }

    private static function gfTables(): array
    {
        static $exp = null;
        static $log = null;

        if ($exp !== null) {
            return [$exp, $log];
        }

        $exp = array_fill(0, 512, 0);
        $log = array_fill(0, 256, 0);
        $x = 1;
        for ($i = 0; $i < 255; $i++) {
            $exp[$i] = $x;
            $log[$x] = $i;
            $x <<= 1;
            if ($x & 0x100) {
                $x ^= 0x11D;
            }
        }
        for ($i = 255; $i < 512; $i++) {
            $exp[$i] = $exp[$i - 255];
        }

        return [$exp, $log];
    }

    private function drawFunctionPatterns(array $alignmentPositions): void
    {
        $this->drawFinderPattern(3, 3);
        $this->drawFinderPattern($this->size - 4, 3);
        $this->drawFinderPattern(3, $this->size - 4);

        for ($i = 8; $i < $this->size - 8; $i++) {
            $dark = $i % 2 === 0;
            $this->setFunction(6, $i, $dark);
            $this->setFunction($i, 6, $dark);
        }

        foreach ($alignmentPositions as $x) {
            foreach ($alignmentPositions as $y) {
                if ($this->isFunction[$y][$x]) {
                    continue;
                }
                $this->drawAlignmentPattern($x, $y);
            }
        }

        $this->reserveFormatAreas();
        $this->setFunction(8, $this->size - 8, true);
    }

    private function drawFinderPattern(int $centerX, int $centerY): void
    {
        for ($dy = -4; $dy <= 4; $dy++) {
            for ($dx = -4; $dx <= 4; $dx++) {
                $x = $centerX + $dx;
                $y = $centerY + $dy;
                if ($x < 0 || $y < 0 || $x >= $this->size || $y >= $this->size) {
                    continue;
                }

                $distance = max(abs($dx), abs($dy));
                $this->setFunction($x, $y, $distance !== 2 && $distance !== 4);
            }
        }
    }

    private function drawAlignmentPattern(int $centerX, int $centerY): void
    {
        for ($dy = -2; $dy <= 2; $dy++) {
            for ($dx = -2; $dx <= 2; $dx++) {
                $distance = max(abs($dx), abs($dy));
                $this->setFunction($centerX + $dx, $centerY + $dy, $distance !== 1);
            }
        }
    }

    private function reserveFormatAreas(): void
    {
        for ($i = 0; $i <= 8; $i++) {
            if ($i !== 6) {
                $this->setFunction(8, $i, false);
                $this->setFunction($i, 8, false);
            }
        }

        for ($i = 0; $i < 8; $i++) {
            $this->setFunction($this->size - 1 - $i, 8, false);
            $this->setFunction(8, $this->size - 1 - $i, false);
        }
    }

    private function setFunction(int $x, int $y, bool $dark): void
    {
        $this->modules[$y][$x] = $dark;
        $this->isFunction[$y][$x] = true;
    }

    private function drawCodewords(array $codewords): void
    {
        $bits = [];
        foreach ($codewords as $codeword) {
            self::appendBits($bits, $codeword, 8);
        }

        $bitIndex = 0;
        $upward = true;
        for ($right = $this->size - 1; $right >= 1; $right -= 2) {
            if ($right === 6) {
                $right--;
            }

            for ($vertical = 0; $vertical < $this->size; $vertical++) {
                $y = $upward ? $this->size - 1 - $vertical : $vertical;
                for ($x = $right; $x >= $right - 1; $x--) {
                    if ($this->isFunction[$y][$x]) {
                        continue;
                    }
                    $this->modules[$y][$x] = ($bits[$bitIndex] ?? 0) === 1;
                    $bitIndex++;
                }
            }

            $upward = !$upward;
        }
    }

    private function applyBestMask(): void
    {
        $bestMask = 0;
        $bestScore = PHP_INT_MAX;
        $bestModules = $this->modules;

        for ($mask = 0; $mask < 8; $mask++) {
            $candidate = $this->applyMaskTo($this->modules, $mask);
            $this->drawFormatBitsTo($candidate, $mask);
            $score = $this->penaltyScore($candidate);

            if ($score < $bestScore) {
                $bestScore = $score;
                $bestMask = $mask;
                $bestModules = $candidate;
            }
        }

        $this->modules = $bestModules;
        $this->drawFormatBitsTo($this->modules, $bestMask);
    }

    private function applyMaskTo(array $modules, int $mask): array
    {
        for ($y = 0; $y < $this->size; $y++) {
            for ($x = 0; $x < $this->size; $x++) {
                if (!$this->isFunction[$y][$x] && $this->mask($mask, $x, $y)) {
                    $modules[$y][$x] = !$modules[$y][$x];
                }
            }
        }
        return $modules;
    }

    private function mask(int $mask, int $x, int $y): bool
    {
        return match ($mask) {
            0 => ($x + $y) % 2 === 0,
            1 => $y % 2 === 0,
            2 => $x % 3 === 0,
            3 => ($x + $y) % 3 === 0,
            4 => (intdiv($y, 2) + intdiv($x, 3)) % 2 === 0,
            5 => (($x * $y) % 2 + ($x * $y) % 3) === 0,
            6 => ((($x * $y) % 2 + ($x * $y) % 3) % 2) === 0,
            7 => ((($x + $y) % 2 + ($x * $y) % 3) % 2) === 0,
            default => false,
        };
    }

    private function drawFormatBitsTo(array &$modules, int $mask): void
    {
        $data = (self::LEVEL_M_FORMAT_BITS << 3) | $mask;
        $bits = self::formatBits($data);

        for ($i = 0; $i <= 5; $i++) {
            $modules[$i][8] = (($bits >> $i) & 1) !== 0;
        }
        $modules[7][8] = (($bits >> 6) & 1) !== 0;
        $modules[8][8] = (($bits >> 7) & 1) !== 0;
        $modules[8][7] = (($bits >> 8) & 1) !== 0;
        for ($i = 9; $i < 15; $i++) {
            $modules[8][14 - $i] = (($bits >> $i) & 1) !== 0;
        }

        for ($i = 0; $i < 8; $i++) {
            $modules[8][$this->size - 1 - $i] = (($bits >> $i) & 1) !== 0;
        }
        for ($i = 8; $i < 15; $i++) {
            $modules[$this->size - 15 + $i][8] = (($bits >> $i) & 1) !== 0;
        }
        $modules[$this->size - 8][8] = true;
    }

    private static function formatBits(int $data): int
    {
        $remainder = $data << 10;
        for ($i = 14; $i >= 10; $i--) {
            if ((($remainder >> $i) & 1) !== 0) {
                $remainder ^= 0x537 << ($i - 10);
            }
        }

        return (($data << 10) | ($remainder & 0x3FF)) ^ 0x5412;
    }

    private function penaltyScore(array $modules): int
    {
        $score = 0;

        for ($y = 0; $y < $this->size; $y++) {
            $runColor = $modules[$y][0];
            $runLength = 1;
            for ($x = 1; $x < $this->size; $x++) {
                if ($modules[$y][$x] === $runColor) {
                    $runLength++;
                } else {
                    if ($runLength >= 5) {
                        $score += 3 + ($runLength - 5);
                    }
                    $runColor = $modules[$y][$x];
                    $runLength = 1;
                }
            }
            if ($runLength >= 5) {
                $score += 3 + ($runLength - 5);
            }
        }

        for ($x = 0; $x < $this->size; $x++) {
            $runColor = $modules[0][$x];
            $runLength = 1;
            for ($y = 1; $y < $this->size; $y++) {
                if ($modules[$y][$x] === $runColor) {
                    $runLength++;
                } else {
                    if ($runLength >= 5) {
                        $score += 3 + ($runLength - 5);
                    }
                    $runColor = $modules[$y][$x];
                    $runLength = 1;
                }
            }
            if ($runLength >= 5) {
                $score += 3 + ($runLength - 5);
            }
        }

        for ($y = 0; $y < $this->size - 1; $y++) {
            for ($x = 0; $x < $this->size - 1; $x++) {
                $color = $modules[$y][$x];
                if ($modules[$y][$x + 1] === $color && $modules[$y + 1][$x] === $color && $modules[$y + 1][$x + 1] === $color) {
                    $score += 3;
                }
            }
        }

        $score += $this->finderLikePenalty($modules);

        $dark = 0;
        foreach ($modules as $row) {
            foreach ($row as $module) {
                if ($module) {
                    $dark++;
                }
            }
        }
        $total = $this->size * $this->size;
        $score += (int) (floor(abs(($dark * 100 / $total) - 50) / 5) * 10);

        return $score;
    }

    private function finderLikePenalty(array $modules): int
    {
        $score = 0;
        $patterns = ['10111010000', '00001011101'];

        for ($y = 0; $y < $this->size; $y++) {
            $line = '';
            for ($x = 0; $x < $this->size; $x++) {
                $line .= $modules[$y][$x] ? '1' : '0';
            }
            foreach ($patterns as $pattern) {
                $score += substr_count($line, $pattern) * 40;
            }
        }

        for ($x = 0; $x < $this->size; $x++) {
            $line = '';
            for ($y = 0; $y < $this->size; $y++) {
                $line .= $modules[$y][$x] ? '1' : '0';
            }
            foreach ($patterns as $pattern) {
                $score += substr_count($line, $pattern) * 40;
            }
        }

        return $score;
    }

    private function toSvg(int $scale, int $border): string
    {
        $dimension = $this->size + ($border * 2);
        $path = [];

        for ($y = 0; $y < $this->size; $y++) {
            for ($x = 0; $x < $this->size; $x++) {
                if ($this->modules[$y][$x]) {
                    $path[] = 'M' . ($x + $border) . ',' . ($y + $border) . 'h1v1h-1z';
                }
            }
        }

        return '<svg xmlns="http://www.w3.org/2000/svg" width="' . ($dimension * $scale) . '" height="' . ($dimension * $scale) . '" viewBox="0 0 ' . $dimension . ' ' . $dimension . '" role="img" aria-label="SmartProfile QR code" shape-rendering="crispEdges">'
            . '<rect width="100%" height="100%" fill="#fff"/>'
            . '<path fill="#111827" d="' . implode('', $path) . '"/>'
            . '</svg>';
    }
}
