//package au.com.m4u.smsapi;

import java.security.cert.X509Certificate;
import javax.net.ssl.X509TrustManager;

class X509TrustAllManager
  implements X509TrustManager
{
  public X509Certificate[] getAcceptedIssuers()
  {
    return null;
  }

  public void checkClientTrusted(X509Certificate[] paramArrayOfX509Certificate, String paramString)
  {
  }

  public void checkServerTrusted(X509Certificate[] paramArrayOfX509Certificate, String paramString)
  {
  }
}