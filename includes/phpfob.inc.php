<?php
/*********
 * This is a consolidated version of phpFob http://github.com/jschilli/phpFob for use
 * in Shine http://github.com/tyler/Shine.
 * phpFob is licensed under the MIT Open Source License
 * The following are copyright notices from the original sources:
 * Encoder.c License ported directly from Samuel Tesla's base32 ruby gem
 * Here's his copyright notice
 * Copyright (c) 2007-2009 Samuel Tesla
 *
 * Permission is hereby granted, free of charge, to any person obtaining a copy
 * of this software and associated documentation files (the "Software"), to deal
 * in the Software without restriction, including without limitation the rights
 * to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
 * copies of the Software, and to permit persons to whom the Software is
 * furnished to do so, subject to the following conditions:
 *
 * The above copyright notice and this permission notice shall be included in
 * all copies or substantial portions of the Software.
 *
 * THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
 * IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
 * FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
 * AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
 * LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
 * OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN
 * THE SOFTWARE.
 */


/* 
 Creates a source string to generate registration code. A source string 
 contains product code name and user's registration name.
*/

function  make_license_source($product_code, $name)
{
  return ($product_code ."," .$name);
}

/*
 Aggregate function to create a license code 
*/
function make_phpFob_license($license_data, $priv_key)
{
    $priv = openssl_pkey_get_private($priv_key);

    $signedData = '';
    openssl_sign($license_data, $signature, $priv, OPENSSL_ALGO_DSS1);
    openssl_free_key($priv);
    $len = strlen($signature);

    $b32 = encode($signature);
    // # Replace Os with 8s and Is with 9s
    // # See http://members.shaw.ca/akochoi-old/blog/2004/11-07/index.html
    $b32 =  str_replace('O', '8', $b32);
    $b32 =  str_replace('I', '9', $b32);
    $b32 = join("-",str_split($b32,5));

    return $b32;
}


/* from decoder.php */
function decode_bits ($bits)
{
  $table = array(
    0xFF, 0xFF, 0xFF, 0xFF, 0xFF, 0xFF, 0xFF, 0xFF, 0xFF, 0xFF, 0xFF, 0xFF, 0xFF, 0xFF,
    0xFF, 0xFF, 0xFF, 0xFF, 0xFF, 0xFF, 0xFF, 0xFF, 0xFF, 0xFF, 0xFF, 0xFF, 0xFF, 0xFF,
    0xFF, 0xFF, 0xFF, 0xFF, 0xFF, 0xFF, 0xFF, 0xFF, 0xFF, 0xFF, 0xFF, 0xFF, 0xFF, 0xFF,
    0xFF, 0xFF, 0xFF, 0xFF, 0xFF, 0xFF, 0xFF, 0xFF, 0x1A, 0x1B, 0x1C, 0x1D, 0x1E, 0x1F,
    0xFF, 0xFF, 0xFF, 0xFF, 0xFF, 0xFF, 0xFF, 0xFF, 0xFF, 0x00, 0x01, 0x02, 0x03, 0x04,
    0x05, 0x06, 0x07, 0x08, 0x09, 0x0A, 0x0B, 0x0C, 0x0D, 0x0E, 0x0F, 0x10, 0x11, 0x12,
    0x13, 0x14, 0x15, 0x16, 0x17, 0x18, 0x19, 0xFF, 0xFF, 0xFF, 0xFF, 0xFF, 0xFF, 0x00,
    0x01, 0x02, 0x03, 0x04, 0x05, 0x06, 0x07, 0x08, 0x09, 0x0A, 0x0B, 0x0C, 0x0D, 0x0E,
    0x0F, 0x10, 0x11, 0x12, 0x13, 0x14, 0x15, 0x16, 0x17, 0x18, 0x19, 0xFF, 0xFF, 0xFF,
    0xFF, 0xFF, 0xFF, 0xFF, 0xFF, 0xFF, 0xFF, 0xFF, 0xFF, 0xFF, 0xFF, 0xFF, 0xFF, 0xFF,
    0xFF, 0xFF, 0xFF, 0xFF, 0xFF, 0xFF, 0xFF, 0xFF, 0xFF, 0xFF, 0xFF, 0xFF, 0xFF, 0xFF,
    0xFF, 0xFF, 0xFF, 0xFF, 0xFF, 0xFF, 0xFF, 0xFF, 0xFF, 0xFF, 0xFF, 0xFF, 0xFF, 0xFF,
    0xFF, 0xFF, 0xFF, 0xFF, 0xFF, 0xFF, 0xFF, 0xFF, 0xFF, 0xFF, 0xFF, 0xFF, 0xFF, 0xFF,
    0xFF, 0xFF, 0xFF, 0xFF, 0xFF, 0xFF, 0xFF, 0xFF, 0xFF, 0xFF, 0xFF, 0xFF, 0xFF, 0xFF,
    0xFF, 0xFF, 0xFF, 0xFF, 0xFF, 0xFF, 0xFF, 0xFF, 0xFF, 0xFF, 0xFF, 0xFF, 0xFF, 0xFF,
    0xFF, 0xFF, 0xFF, 0xFF, 0xFF, 0xFF, 0xFF, 0xFF, 0xFF, 0xFF, 0xFF, 0xFF, 0xFF, 0xFF,
    0xFF, 0xFF, 0xFF, 0xFF, 0xFF, 0xFF, 0xFF, 0xFF, 0xFF, 0xFF, 0xFF, 0xFF, 0xFF, 0xFF,
    0xFF, 0xFF, 0xFF, 0xFF, 0xFF, 0xFF, 0xFF, 0xFF, 0xFF, 0xFF, 0xFF, 0xFF, 0xFF, 0xFF,
    0xFF, 0xFF, 0xFF, 0xFF
  );
  return $table[ord($bits)];
}


function arrayToString($inputArray,$count)
{
    $outputString = '';
    $char = '';
    for ($i = 0 ; $i < $count;$i++) {
        $char = chr($inputArray[$i]);
        $outputString .= $char;
    }
    return $outputString;
}

function base32_decode_buffer_size ($encodedTextLength)
{
  if ($encodedTextLength == 0 || $encodedTextLength % 8 != 0)
    return 0;
  return intval(($encodedTextLength * 5) / 8);
}

function base32_decode ($input,$outputLength)
{
    $inputLength = strlen($input);
    $bytes = 0;
  $currentByte = 0;
    $output = '';
    for ($offset = 0; $offset < $inputLength  && $bytes < $outputLength; $offset += 8)
    {
        $output[$bytes] = decode_bits ($input{$offset + 0}) << 3;
      $currentByte = decode_bits ($input{$offset + 1});
      $output[$bytes] += $currentByte >> 2;
      $output[$bytes + 1] = ($currentByte & 0x03) << 6;

      if ($input{$offset + 2} == '='){
        return arrayToString($output,$bytes+1);
        } else
        $bytes++;

        $output[$bytes] += decode_bits ($input{$offset + 2}) << 1;
        $currentByte = decode_bits ($input{$offset + 3});
        $output[$bytes] += $currentByte >> 4;
        $output[$bytes + 1] = $currentByte << 4;

        if ($input{$offset + 4} == '='){
        return arrayToString($output,$bytes+1);
        } else
        $bytes++;

       $currentByte = decode_bits ($input{$offset + 4});
       $output[$bytes] += $currentByte >> 1;
       $output[$bytes + 1] = $currentByte << 7;

       if ($input{$offset + 5} == '='){
         return arrayToString($output,$bytes+1);
         } else
         $bytes++;

       $output[$bytes] += decode_bits ($input{$offset + 5}) << 2;
       $currentByte = decode_bits ($input{$offset + 6});
       $output[$bytes] +=  $currentByte >> 3;
       $output[$bytes + 1] = ($currentByte & 0x07) << 5;

      if ($input{$offset + 7} == '='){
        return arrayToString($output,$bytes+1);
        } else
        $bytes++;

       $output[$bytes] += decode_bits ($input{$offset + 7}) & 0x1F;
       $bytes++;
    }
  return arrayToString($output,$bytes);
}


/* from encoder.php */
function base32_encoder_last_quintet($length)
{
    $quintets = intval($length * 8 / 5);
    $remainder = $length % 5;
    if ($remainder!=0) {
        $quintets++;        
    }
    return $quintets;
}

function ord_value($buffer, $offset) 
{
    return ord(substr($buffer,$offset,1));
}

function base32_encoder_encode_bits($position, $buffer)
{
    $offset = intval(($position / 8)) * 5;
    switch ($position % 8) {
        case 0:
            return ((ord_value($buffer,$offset) & 0xF8) >> 3);
         case 1:
              return
                ((ord_value($buffer,$offset) & 0x07) << 2) +
                ((ord_value($buffer,$offset+1) & 0xC0) >> 6);

            case 2:
              return
                ((ord_value($buffer,$offset+1) & 0x3E) >> 1);

            case 3:
              return
                ((ord_value($buffer,$offset+1) & 0x01) << 4) +
                ((ord_value($buffer,$offset+2) & 0xF0) >> 4);

            case 4:
              return
                ((ord_value($buffer,$offset+2) & 0x0F) << 1) +
                ((ord_value($buffer,$offset+3) & 0x80) >> 7);

            case 5:
              return
                ((ord_value($buffer,$offset+3) & 0x7C) >> 2);

            case 6:
              return
                ((ord_value($buffer,$offset+3) & 0x03) << 3) +
                ((ord_value($buffer,$offset+4) & 0xE0) >> 5);

            case 7:
              return
                ord_value($buffer,$offset+4) & 0x1F;
            
    }
}


function base32_encoder_encode_at_position ($position, $buffer)
{
  $table = "ABCDEFGHIJKLMNOPQRSTUVWXYZ234567";
  $index = base32_encoder_encode_bits ($position, $buffer);
  return substr($table,$index,1);
}

function encode($number)
{
    $quintets = base32_encoder_last_quintet(strlen($number));
    $output = '';
    for ($i=0; $i < $quintets;$i++) {
        $output .= base32_encoder_encode_at_position($i,$number);
    }
    
    return $output;
}
