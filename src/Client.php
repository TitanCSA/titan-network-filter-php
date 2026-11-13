<?php
declare(strict_types=1);
namespace TitanCSA\Products\TitanNetworkFilter;
use FFI;
final class NativeException extends \RuntimeException {}
final class Client {
 private FFI $ffi;
 private int $session=0;
 public function __construct(?string $libraryPath=null){
  if(PHP_OS_FAMILY!=='Windows' || PHP_INT_SIZE!==8)throw new \RuntimeException('Windows x64 PHP required');
  $this->ffi=FFI::cdef('unsigned int titan_network_filter_json_open(uint64_t*);unsigned int titan_network_filter_json_close(uint64_t);char* titan_network_filter_json_call(uint64_t,const char*,uint32_t);void titan_network_filter_json_free(char*);',$libraryPath??(getenv('TITAN_NETWORK_FILTER_SDK_NATIVE_PATH')?:dirname(__DIR__).'/native/TitanNetworkFilterJsonSDK.dll'));
  $id=$this->ffi->new('uint64_t');$code=$this->ffi->titan_network_filter_json_open(FFI::addr($id));if($code)throw new NativeException('Cannot open SDK',$code);$this->session=$id->cdata;
 }
 public function call(string $operation,array|object $arguments=[]):mixed{
  if(!$this->session)throw new \LogicException('SDK is closed');
  $payload=json_encode(['operation'=>$operation,'arguments'=>$arguments===[]?(object)[]:$arguments],JSON_THROW_ON_ERROR|JSON_UNESCAPED_UNICODE);
  if(strlen($payload)>32*1024*1024)throw new \InvalidArgumentException('Request exceeds 32 MiB');
  $pointer=$this->ffi->titan_network_filter_json_call($this->session,$payload,strlen($payload));if(FFI::isNull($pointer))throw new \RuntimeException('Native response allocation failed');
  try{$response=json_decode(FFI::string($pointer),true,512,JSON_THROW_ON_ERROR);}finally{$this->ffi->titan_network_filter_json_free($pointer);}
  if(!$response['ok'])throw new NativeException($response['message'],$response['code']);return $response['result'];
 }
 public function close():void{if($this->session){$code=$this->ffi->titan_network_filter_json_close($this->session);if($code)throw new NativeException('Native close failed',$code);$this->session=0;}}
 public function __destruct(){try{$this->close();}catch(\Throwable){}}
}
