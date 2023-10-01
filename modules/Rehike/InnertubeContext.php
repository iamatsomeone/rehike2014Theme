<?php
namespace Rehike;

use Rehike\Util\Base64Url;

/**
 * Generates a valid InnerTube client context required
 * for requesting the API.
 * 
 * TODO: Deprecate?
 * 
 * @author Taniko Yamamoto <kirasicecreamm@gmail.com>
 * @author The Rehike Maintainers
 */
class InnertubeContext
{
    /**
     * Convert an integer to a ULEB128 binary for use in
     * synthesising protobuf.
     * 
     * This should be deprecated in the future.
     * 
     * @param int $int to convert
     * @return string uleb128 binary
     */
    public static function int2uleb128(int $int): string
    {
        // this is awful
        // i hate the person who wrot ethis
        
        if ($int < 128) return chr($int);
        
        $out = decbin($int);
        
        while (0 != strlen($out) % 7)
        {
            $out = '0' . $out;
        }
        
        $out = str_split($out, 7);
        
        
        for ($i = 0; $i < count($out); $i++)
        {
            if (0 != $i)
            {
                $out[$i] = '1' . $out[$i];
            }
            else
            {
                $out[$i] = '0' . $out[$i];
            }
        }
        
        $out = array_reverse($out);
        $out = implode('', $out);
        
        $out = str_split($out, 8);
        
        $out2 = "";
        
        for ($i = 0; $i < count($out); $i++)
        {
            $out2 .= chr( bindec($out[$i]) );
        }
        
        return $out2;
    }

    /**
     * Generate an encoded YouTube visitor data string.
     * 
     * @param string $visitor
     * @return string encoded visitor data
     */
    public static function genVisitorData(string $visitor): string
    {
        // Generate visitorData string
        if (is_null($visitor)) return "";
        
        $date = time();
        
        return Base64Url::encode(
            chr(0x0a) . self::int2uleb128( strlen($visitor) ) . $visitor . chr(0x28) . self::int2uleb128($date)
        );
    }

    /**
     * Generate an InnerTube context template.
     * 
     * @param string|int $cname (client name) enum or index
     * @param string $cver (client version) number
     * @param string|null $visitorData
     * @param string $hl (host language)
     * @param string $gl (global location)
     * 
     * @return object InnerTube context
     */
    public static function generate(
            string|int $cname, 
            string $cver, 
            ?string $visitorData = null, 
            string $hl = "en", 
            string $gl = "US"
    ): object
    {
        if (is_null($visitorData)) $visitorData = ContextManager::$visitorData;
        return (object) [
            'context' => (object) [
                'client' => (object) [
                    'hl' => $hl,
                    'gl' => $gl,
                    'visitorData' => self::genVisitorData($visitorData),
                    'clientName' => $cname,
                    'clientVersion' => $cver,
                    'userAgent' => $_SERVER['HTTP_USER_AGENT']  ?? ""
                ],
                'user' => (object) [
                    'lockedSafetyMode' => false
                ],
                'request' => (object) [
                    'useSsl' => true,
                    'internalExperimentFlags' => [],
                    'consistencyTokenJars' => []
                ]
            ]
        ];
    }
}