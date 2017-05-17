<?php
namespace Home\Tool;
class RsaTool {    
    private static $PRIVATE_KEY = '-----BEGIN RSA PRIVATE KEY-----
MIICXwIBAAKBgQC1YTwE1hsKWiA1bTi9TPD2vSfO+d0fQcLgkIohwauEXztdoOQT
xEtHGMhrOWt57AYHz3/aoWFHgrcl/WV1eS7qIZVGTHMZ1cfPZa3wylZLmPKLeNgZ
jvrlPPlHo9JyDH8NGQBHwnLdyvV8Jwgrg9p/puaHFac2JWFBoD/5/VC8JwIDAQAB
AoGBAI6vo2uak0Mdi2D8PzqMILO6MmrcnVtNfGT0z/WmSaukzyrkuwfhz+ZqVKAl
V6teRZA3aDkx4zlCV5oHMZ2sa35oorj/Xg07sMV8G21CqlFsDl1bwxysv18o6UAM
v50U1ySSflSvrGlZBFcje/xz2OUDodAcyini49gUVkq0f9+hAkEA5V+ZabDvulKE
Db671saXlWhxrwO7Si7gTXQbLopGFlVEgt13T1Z4NdgAYKYOsdWgbEfkvoGPDvuS
pZGJU7hfZQJBAMpvYspfbZHJEOK0d+pT4PMTM0zlWRXiBtdJYnPkrKUBori4k7jN
BgonJ8y5OihAsRZRZHpAacnXQktE6ygkcpsCQQCxpCxoUoQXRTjIfGN1nzBeohkJ
dlZiyZMl6Tnz/VryiO8aevKgG1PWP4drUrAmwlAQDE33zNdCv0t/twsoL66RAkEA
x1ZDe2/YnxPU0shOxKnv+qvPekrlHE1D2z7h+akV8C3aI/dtTy5kYh8Ia+mBQR3i
w01GmbNP+HdFSoUE4rRxPQJBANnF79V5DmvdfqBT5MS+BSzl9z6RcC050wmnzYiq
rkbgdxlKeBsSU71gR0omgOro2h2Yur5qe3K7JJGjPCokzVQ=
-----END RSA PRIVATE KEY-----';       
    private static $PUBLIC_KEY = '-----BEGIN PUBLIC KEY-----
MIGfMA0GCSqGSIb3DQEBAQUAA4GNADCBiQKBgQC1YTwE1hsKWiA1bTi9TPD2vSfO
+d0fQcLgkIohwauEXztdoOQTxEtHGMhrOWt57AYHz3/aoWFHgrcl/WV1eS7qIZVG
THMZ1cfPZa3wylZLmPKLeNgZjvrlPPlHo9JyDH8NGQBHwnLdyvV8Jwgrg9p/puaH
Fac2JWFBoD/5/VC8JwIDAQAB
-----END PUBLIC KEY-----';   
    /* 获取私钥     
     * @return bool|resource     
     */    
    private static function getPrivateKey() 
    {        
        $privKey = self::$PRIVATE_KEY;        
        return openssl_pkey_get_private($privKey);    
    }    

    /**     
     * 获取公钥     
     * @return bool|resource     
     */    
    private static function getPublicKey()
    {        
        $publicKey = self::$PUBLIC_KEY;        
        return openssl_pkey_get_public($publicKey);    
    }    

    /**     
     * 私钥加密     
     * @param string $data     
     * @return null|string     
     */    
    public static function privEncrypt($data = '')    
    {        
        if (!is_string($data)) {            
            return null;       
        }        
        return openssl_private_encrypt($data,$encrypted,self::getPrivateKey()) ? base64_encode($encrypted) : null;    
    }    

    /**     
     * 公钥加密     
     * @param string $data     
     * @return null|string     
     */    
    public static function publicEncrypt($data = '')   
    {        
        if (!is_string($data)) {            
            return null;        
        }        
        return openssl_public_encrypt($data,$encrypted,self::getPublicKey()) ? base64_encode($encrypted) : null;    
    }    

    /**     
     * 私钥解密     
     * @param string $encrypted     
     * @return null     
     */    
    public static function privDecrypt($encrypted = '')    
    {        
        if (!is_string($encrypted)) {            
            return null;        
        }        
        return (openssl_private_decrypt(base64_decode($encrypted), $decrypted, self::getPrivateKey())) ? $decrypted : null;    
    }    

    /**     
     * 公钥解密     
     * @param string $encrypted     
     * @return null     
     */    
    public static function publicDecrypt($encrypted = '')    
    {        
        if (!is_string($encrypted)) {            
            return null;        
        }        
    return (openssl_public_decrypt(base64_decode($encrypted), $decrypted, self::getPublicKey())) ? $decrypted : null;    
    }
}
