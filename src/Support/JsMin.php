<?php

namespace Npds\Support;

/**
 * Undocumented class
 */
class JSMin
{

    /**
     * 
     */
    const ORD_LF            = 10;

    /**
     * 
     */
    const ORD_SPACE         = 32;

    /**
     * 
     */
    const ACTION_KEEP_A     = 1;

    /**
     * 
     */
    const ACTION_DELETE_A   = 2;

    /**
     * 
     */
    const ACTION_DELETE_A_B = 3;

    /**
     * Undocumented variable
     *
     * @var string
     */
    protected $a           = "\n";

    /**
     * Undocumented variable
     *
     * @var string
     */
    protected $b           = '';

    /**
     * Undocumented variable
     *
     * @var string
     */
    protected $input       = '';

    /**
     * Undocumented variable
     *
     * @var integer
     */
    protected $inputIndex  = 0;

    /**
     * Undocumented variable
     *
     * @var integer
     */
    protected $inputLength = 0;

    /**
     * Undocumented variable
     *
     * @var [type]
     */
    protected $lookAhead   = null;

    /**
     * Undocumented variable
     *
     * @var string
     */
    protected $output      = '';

    /**
     * Undocumented variable
     *
     * @var string
     */
    protected $lastByteOut  = '';

    /**
     * Undocumented variable
     *
     * @var string
     */
    protected $keptComment = '';


    /**
     * Undocumented function
     *
     * @param [type] $js
     * @return void
     */
    public static function minify($js)
    {
        $jsmin = new JSMin($js);


        return $jsmin->min();
    }

    /**
     * Undocumented function
     *
     * @param [type] $input
     */
    public function __construct($input)
    {
        $this->input = $input;
    }

    /**
     * Undocumented function
     *
     * @return void
     */
    public function min()
    {
        if ($this->output !== '') { // min already run
            return $this->output;
        }

        $mbIntEnc = null;
        if (function_exists('mb_strlen') && ((int)ini_get('mbstring.func_overload') & 2)) {
            $mbIntEnc = mb_internal_encoding();
            mb_internal_encoding('8bit');
        }
        $this->input = str_replace("\r\n", "\n", $this->input);
        $this->inputLength = strlen($this->input);

        $this->action(self::ACTION_DELETE_A_B);

        while ($this->a !== null) {
            // determine next command
            $command = self::ACTION_KEEP_A; // default
            if ($this->a === ' ') {
                if (($this->lastByteOut === '+' || $this->lastByteOut === '-')
                        && ($this->b === $this->lastByteOut)) {
                    // Don't delete this space. If we do, the addition/subtraction
                    // could be parsed as a post-increment
                } elseif (! $this->isAlphaNum($this->b)) {
                    $command = self::ACTION_DELETE_A;
                }
            } elseif ($this->a === "\n") {
                if ($this->b === ' ') {
                    $command = self::ACTION_DELETE_A_B;

                    // in case of mbstring.func_overload & 2, must check for null b,
                    // otherwise mb_strpos will give WARNING
                } elseif ($this->b === null
                          || (false === strpos('{[(+-!~', $this->b)
                              && ! $this->isAlphaNum($this->b))) {
                    $command = self::ACTION_DELETE_A;
                }
            } elseif (! $this->isAlphaNum($this->a)) {
                if ($this->b === ' '
                    || ($this->b === "\n"
                        && (false === strpos('}])+-\'', $this->a)))) {
                    $command = self::ACTION_DELETE_A_B;
                }
            }
            $this->action($command);
        }
        $this->output = trim($this->output);

        if ($mbIntEnc !== null) {
            mb_internal_encoding($mbIntEnc);
        }
        return $this->output;
    }

    /**
     * Undocumented function
     *
     * @param [type] $command
     * @return void
     */
    protected function action($command)
    {
        // make sure we don't compress "a + ++b" to "a+++b", etc.
        if ($command === self::ACTION_DELETE_A_B
            && $this->b === ' '
            && ($this->a === '+' || $this->a === '-')) {
            // Note: we're at an addition/substraction operator; the inputIndex
            // will certainly be a valid index
            if ($this->input[$this->inputIndex] === $this->a) {
                // This is "+ +" or "- -". Don't delete the space.
                $command = self::ACTION_KEEP_A;
            }
        }

        switch ($command) {
            case self::ACTION_KEEP_A:
                $this->output .= $this->a;

                if ($this->keptComment) {
                    $this->output = rtrim($this->output, "\n");
                    $this->output .= $this->keptComment;
                    $this->keptComment = '';
                }

                $this->lastByteOut = $this->a;

                // fallthrough intentional
                break;
            case self::ACTION_DELETE_A:
                $this->a = $this->b;
                if ($this->a === "'" || $this->a === '"') { // string literal
                    $str = $this->a; // in case needed for exception
                    for (;;) {
                        $this->output .= $this->a;
                        $this->lastByteOut = $this->a;

                        $this->a = $this->get();
                        if ($this->a === $this->b) { // end quote
                            break;
                        }
                        if ($this->isEOF($this->a)) {
                            $byte = $this->inputIndex - 1;
                            throw new \Exception(
                                "JSMin: Unterminated String at byte {$byte}: {$str}"
                            );
                        }
                        $str .= $this->a;
                        if ($this->a === '\\') {
                            $this->output .= $this->a;
                            $this->lastByteOut = $this->a;

                            $this->a       = $this->get();
                            $str .= $this->a;
                        }
                    }
                }

                // fallthrough intentional
                break;
            case self::ACTION_DELETE_A_B:
                $this->b = $this->next();
                if ($this->b === '/' && $this->isRegexpLiteral()) {
                    $this->output .= $this->a . $this->b;
                    $pattern = '/'; // keep entire pattern in case we need to report it in the exception
                    for (;;) {
                        $this->a = $this->get();
                        $pattern .= $this->a;
                        if ($this->a === '[') {
                            for (;;) {
                                $this->output .= $this->a;
                                $this->a = $this->get();
                                $pattern .= $this->a;
                                if ($this->a === ']') {
                                    break;
                                }
                                if ($this->a === '\\') {
                                    $this->output .= $this->a;
                                    $this->a = $this->get();
                                    $pattern .= $this->a;
                                }
                                if ($this->isEOF($this->a)) {
                                    throw new \Exception("JSMin: Unterminated set in RegExp at byte "
                                            . $this->inputIndex .": {$pattern}");
                                }
                            }
                        }

                        if ($this->a === '/') { // end pattern
                            break; // while (true)
                        } elseif ($this->a === '\\') {
                            $this->output .= $this->a;
                            $this->a = $this->get();
                            $pattern .= $this->a;
                        } elseif ($this->isEOF($this->a)) {
                            $byte = $this->inputIndex - 1;
                            throw new \Exception("JSMin: Unterminated RegExp at byte {$byte}: {$pattern}");
                        }
                        $this->output .= $this->a;
                        $this->lastByteOut = $this->a;
                    }
                    $this->b = $this->next();
                }
            // end case ACTION_DELETE_A_B
        }
    }

    /**
     * Undocumented function
     *
     * @return boolean
     */
    protected function isRegexpLiteral()
    {
        if (false !== strpos("(,=:[!&|?+-~*{;", $this->a)) {
            // we obviously aren't dividing
            return true;
        }

        // we have to check for a preceding keyword, and we don't need to pattern
        // match over the whole output.
        $recentOutput = substr($this->output, -10);

        // check if return/typeof directly precede a pattern without a space
        foreach (array('return', 'typeof') as $keyword) {
            if ($this->a !== substr($keyword, -1)) {
                // certainly wasn't keyword
                continue;
            }
            if (preg_match("~(^|[\\s\\S])" . substr($keyword, 0, -1) . "$~", $recentOutput, $m)) {
                if ($m[1] === '' || !$this->isAlphaNum($m[1])) {
                    return true;
                }
            }
        }

        // check all keywords
        if ($this->a === ' ' || $this->a === "\n") {
            if (preg_match('~(^|[\\s\\S])(?:case|else|in|return|typeof)$~', $recentOutput, $m)) {
                if ($m[1] === '' || !$this->isAlphaNum($m[1])) {
                    return true;
                }
            }
        }

        return false;
    }

    /**
     * Undocumented function
     *
     * @return void
     */
    protected function get()
    {
        $c = $this->lookAhead;
        $this->lookAhead = null;
        if ($c === null) {
            // getc(stdin)
            if ($this->inputIndex < $this->inputLength) {
                $c = $this->input[$this->inputIndex];
                $this->inputIndex += 1;
            } else {
                $c = null;
            }
        }
        if (ord($c) >= self::ORD_SPACE || $c === "\n" || $c === null) {
            return $c;
        }
        if ($c === "\r") {
            return "\n";
        }
        return ' ';
    }

    /**
     * Undocumented function
     *
     * @param [type] $a
     * @return boolean
     */
    protected function isEOF($a)
    {
        return ord($a) <= self::ORD_LF;
    }

    /**
     * Undocumented function
     *
     * @return void
     */
    protected function peek()
    {
        $this->lookAhead = $this->get();
        return $this->lookAhead;
    }

    /**
     * Undocumented function
     *
     * @param [type] $c
     * @return boolean
     */
    protected function isAlphaNum($c)
    {
        return (preg_match('/^[a-z0-9A-Z_\\$\\\\]$/', $c) || ord($c) > 126);
    }

    /**
     * Undocumented function
     *
     * @return void
     */
    protected function consumeSingleLineComment()
    {
        $comment = '';
        while (true) {
            $get = $this->get();
            $comment .= $get;
            if (ord($get) <= self::ORD_LF) { // end of line reached
                // if IE conditional comment
                if (preg_match('/^\\/@(?:cc_on|if|elif|else|end)\\b/', $comment)) {
                    $this->keptComment .= "/{$comment}";
                }
                return;
            }
        }
    }

    /**
     * Undocumented function
     *
     * @return void
     */
    protected function consumeMultipleLineComment()
    {
        $this->get();
        $comment = '';
        for (;;) {
            $get = $this->get();
            if ($get === '*') {
                if ($this->peek() === '/') { // end of comment reached
                    $this->get();
                    if (0 === strpos($comment, '!')) {
                        // preserved by YUI Compressor
                        if (!$this->keptComment) {
                            // don't prepend a newline if two comments right after one another
                            $this->keptComment = "\n";
                        }
                        $this->keptComment .= "/*!" . substr($comment, 1) . "*/\n";
                    } else if (preg_match('/^@(?:cc_on|if|elif|else|end)\\b/', $comment)) {
                        // IE conditional
                        $this->keptComment .= "/*{$comment}*/";
                    }
                    return;
                }
            } elseif ($get === null) {
                throw new \Exception("JSMin: Unterminated comment at byte {$this->inputIndex}: /*{$comment}");
            }
            $comment .= $get;
        }
    }

    /**
     * Undocumented function
     *
     * @return void
     */
    protected function next()
    {
        $get = $this->get();
        if ($get === '/') {
            switch ($this->peek()) {
                case '/':
                    $this->consumeSingleLineComment();
                    $get = "\n";
                    break;
                case '*':
                    $this->consumeMultipleLineComment();
                    $get = ' ';
                    break;
            }
        }
        return $get;
    }

}
