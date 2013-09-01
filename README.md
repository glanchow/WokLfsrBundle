WokLfsrBundle
=============

Linear Feedback Shift Register (LFSR) for Symfony2



Overview
--------



Installation
------------

Edit composer.json

```json
"require": {
...
    "glanchow/wok-lfsr-bundle": "*"
```

Then update

```bash
php composer.phar update
```



Configuration
-------------

```php
// src app/AppKernel.php

public function registerBundles()
...
    $bundles = array(
    ...
               new Wok\LfsrBundle\WokLfsrBundle(),
```

```yml
# src app/config/config.yml

# Wok LSFR Configuration
wok_lfsr:
    feedback: 0xC
    state: 1
    base: null
    pad: false
```



Find your feedback term
-----------------------

Here's a list with taps for n up to 4096:
http://www.eej.ulst.ac.uk/~ian/modules/EEE515/files/old_files/lfsr/lfsr_table.pdf

Here's a list with feedback terms for n up to 40, but with multiple and differents feedback terms:
https://www.ece.cmu.edu/~koopman/lfsr/index.html

Let's say you want a maximum-length feedback term for a 8 bit LFSR.

For n = 8, possible taps are 8, 6, 5, 4.

```bash
$ bc
obase=16
ibase=2
10111000
B8
```

Or more simply:

```bash
$ bc
obase=16
2^7 + 2^5 + 2^4 + 2^3
B8
```

0xB8 is your feedback term.

### A little feedback term table

|   n |           feedback |
| ---:| ------------------:|
|   4 |                0xC |
|   5 |               0x1E |
|   6 |               0x36 |
|   7 |               0x78 |
|   8 |               0xB8 |
|  16 |             0xB400 |
|  30 |         0x32800000 |
|  31 |         0x78000000 |
|  32 |         0xA3000000 |
|  63 | 0x6600000000000000 |
|  64 | 0xD800000000000000 |



Usage
-----

### Create an instance

Using default or global configuration:

```php
$lfsr = $this->get('wok_lfsr');
```

Using a custom configuration:

```php
$config = array(
    'feedback' => 0xC,
    'state' => '11',
    'base' => '01',
    'pad' => true
);

$lfsr = $this->get('wok_lfsr')->config($config);
```

### Next state

```php
$state = $lfsr->next();
```



Patterns
--------

### Run a complete cycle:

```php
$config = array(
    'feedback' => 0xC,
    'state' => 0x1,
    'base' => null,
    'pad' => false
    );
$lfsr = $this->get('wok_lfsr')->config($config);

$lfsr->setState(4);
$end = 4;
$iterations = 0;
do {
    $iterations++;
    $state = $lfsr->next();
    echo $state . "<br/>";
} while ($state != $end);
echo "$iterations iterations<br/>";
```



Cookbook
--------

### Random looking identifier

LFSR can be used to generate random looking identifiers for database records (mysql, postgresql, etc).

Let's say you want a short random looking identifier for one of a billion records.

```yml
# src app/config/config.yml

wok_lfsr:
    feedback: 0x32800000
    state: 1
    base: 0123456789bcdfghjklmnpqrstvwxzBCDFGHJKLMNPQRSTVWXZ
    pad: true
```

Let's explain this configuration.

For a billion record we need at least 30 bits.

We've found a 30 bits feedback term over the www: 0x32800000.

The object automatically finds that this is a feedback term for a 30 bit LFSR,
it's easy since a n bit LFSR needs a n tap.

To get a short word, we selected a 50 symbols base.

The object automatically finds that we need 6 symbols (50^6) to write all (2^30) words,
and takes for padding symbol, the first symbol of the base.



Warning
-------

### Code and processor limitation

If your feedback term is greater than the server's PHP_INT_MAX, which is processor dependant,
it will be converted to a float number and the current code will provide wrong results.

### Unsigned int

PHP doesn't use the unsigned int type.
In the case you want only positive numbers or use a custom base,
please use at most n -1 bits of your processor capacity.
