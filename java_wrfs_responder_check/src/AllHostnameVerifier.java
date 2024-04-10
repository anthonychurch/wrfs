//package au.com.m4u.smsapi;

import javax.net.ssl.HostnameVerifier;
import javax.net.ssl.SSLSession;

class AllHostnameVerifier
  implements HostnameVerifier
{
  public boolean verify(String paramString, SSLSession paramSSLSession)
  {
    return true;
  }
}