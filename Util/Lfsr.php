<?php
namespace Wok\LfsrBundle\Util;

use Symfony\Component\Process\Exception\InvalidArgumentException;

/**
 * Galois LFSR simulator
 */
class Lfsr {

    /** @param integer $feedback lfsr feedback term */
    private $feedback;

    /** @param string $state counter's current state */
    private $state;

    /** @param string $base custom alphabet */
    private $base;

    /** @param bool $pad use string padding */
    private $pad;

    /**
     * Instantiate with given config
     *
     * @param integer $feedback
     * @param string $state
     * @param string $base
     * @param bool $pad
     */
    public function __construct($feedback, $state, $base, $pad)
    {
        $this->setFeedback($feedback);
        $this->setState($state);
        $this->base = $base;
        $this->setPad($pad);
    }

    /*
     * Set configuration
     *
     * @param string $config
     * @return \Wok\LfsrBundle\Util\Lfsr
     */
    public function config($config = array())
    {
        if (array_key_exists('feedback', $config)) {
            $this->setFeedback($config['feedback']);
        }
        if (array_key_exists('state', $config)) {
            $this->setState($config['state']);
        }
        if (array_key_exists('base', $config)) {
            $this->base = $config['base'];
        }
        if (array_key_exists('pad', $config)) {
            $this->setPad($config['pad']);
        }
        return $this;
    }

    /**
     * Convert a number as string from a base to another base
     * Bases are given in extended format, i.e: full string format
     * src http://php.net/manual/fr/function.base-convert.php
     *
     * Example:
     *     Convert '1111' from binary to hexadecimal
     *     convbase('1111', '01', '0123456789ABCDEF')
     *     Send a message with a secret alphabet
     *     convbase('FOO', 'ABCDE…XYZ', 'AEIOU')
     *
     * @param string $numberInput
     * @param string $fromBaseInput
     * @param string $toBaseInput
     * @return string
     */
    private function convBase($numberInput, $fromBaseInput, $toBaseInput)
    {
        if ($fromBaseInput == $toBaseInput) {
            return $numberInput;
        }

        $fromBase = str_split($fromBaseInput, 1);
        $toBase = str_split($toBaseInput, 1);
        $number = str_split($numberInput, 1);
        $fromLen = strlen($fromBaseInput);
        $toLen = strlen($toBaseInput);
        $numberLen = strlen($numberInput);
        $retval='';

        if ($toBaseInput == '0123456789')
        {
            $retval = 0;
            for ($i = 1; $i <= $numberLen; $i++) {
                $retval = bcadd(
                    $retval,
                    bcmul(
                        array_search($number[$i - 1], $fromBase),
                        bcpow($fromLen, $numberLen - $i)
                        )
                    );
            }
            return $retval;
        }

        if ($fromBaseInput != '0123456789') {
            $base10 = convBase($numberInput, $fromBaseInput, '0123456789');
        }
        else {
            $base10 = $numberInput;
        }

        if ($base10 < strlen($toBaseInput)) {
            return $toBase[$base10];
        }

        while($base10 != '0') {
            $retval = $toBase[bcmod($base10, $toLen)] . $retval;
            $base10 = bcdiv($base10, $toLen, 0);
        }

        return $retval;
    }

    /**
     * Set the feedback term and compute the bit mask
     *
     * @param integer $feedback feedback term
     * @throw Symfony\Component\Process\Exception\InvalidArgumentException
     * @return \Wok\LfsrBundle\Util\Lfsr
     */
    public function setFeedback($feedback)
    {
        if (! is_int($feedback) || $feedback < 1) {
            throw new InvalidArgumentException('Invalid feedback value');
        }
        $this->feedback = $feedback;
        $mask = 1;
        while ($feedback >>= 1) {
            $mask <<= 1;
            $mask += 1;
        }
        $this->mask = $mask;
        return $this;
    }

    /**
     * Set the counter state
     *
     * @param integer|number $state
     * @throw Symfony\Component\Process\Exception\InvalidArgumentException
     * @return \Wok\LfsrBundle\Util\Lfsr
     */
    public function setState($state)
    {
        if ($this->base === null && $state === 0) {
            throw new InvalidArgumentException('State 0 is invalid.');
        }
        if ($this->base !== null && ! is_string($state)) {
            throw new InvalidArgumentException('When using a base, state must be a string.');
        }
        if ($this->base !== null && $state === $this->base[0]) {
            throw new InvalidArgumentException('State ' . $state . ' is invalid.');
        }
        $this->state = $state;
        return $this;
    }

    /**
     * Set to on/off and compute the padding
     *
     * @param bool $pad
     * @throw InvalidArgumentException
     * @return \Wok\LfsrBundle\Util\Lfsr
     */
    public function setPad($pad)
    {
        if ($this->base !== null && $pad === true) {
            $mask = $this->convBase($this->mask, '0123456789', $this->base);
            $this->pad = strlen($mask);
        }
        else {
            $this->pad = false;
        }
        return $this;
    }

    /**
     * Process to next state
     *
     * @return integer|string
     */
    public function next()
    {
        if ($this->base != null) {
            $base10 = '0123456789';
            $state = $this->convBase($this->state, $this->base, $base10);
        }
        else {
            $state = $this->state;
        }

        $next = ($state >> 1) ^ (-($state &1) & $this->feedback);
        $next &= $this->mask;

        if ($this->base != null) {
            $next = $this->convBase(strval($next), $base10, $this->base);
            if ($this->pad != null) {
                $next = str_pad($next, $this->pad, $this->base[0], STR_PAD_LEFT);
            }
        }

        $this->state = $next;

        return $next;
    }

}
