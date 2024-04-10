import java.io.*;
import java.net.InetAddress;
import java.util.Properties;
import java.util.Date; 
import javax.mail.*;
import javax.mail.internet.*;
import javax.activation.*;

public class SMS_Ipipi_Utilities{
	private static String username = null;//"YoureIPIPIUsername";
	private static String password = null;//"YourPassword";
	private static String smtphost = "ipipi.com";
	private static String from = null;//"YoureIPIPIUsername@ipipi.com";
	private static String compression = "Compression Option goes here - find out more";
	

    public SMS_Ipipi_Utilities(String usrname, String pssword) {
    	username = usrname;
    	password = pssword;
    	from = usrname + "@ipipi.com";
    }

    public String[] sendMessage(String[] to, String message ) {
        Transport tr = null;
        String[] confirmation = new String[to.length];
        for(int i = 0; i < to.length; i++){

        	try {
        		Properties props = System.getProperties();
        		props.put("mail.smtp.auth", "true");

        		// Get a Session object
        		Session mailSession = Session.getDefaultInstance(props, null);

        		// construct the message
        		Message msg = new MimeMessage(mailSession);

        		//Set message attributes
        		msg.setFrom(new InternetAddress(from));
        		InternetAddress[] address = {new InternetAddress(to[i])};
        		msg.setRecipients(Message.RecipientType.TO, address);
        		msg.setSubject(compression);
        		msg.setText(message);
         		msg.setSentDate(new Date());

         		tr = mailSession.getTransport("smtp");
         		tr.connect(smtphost, username, password);
         		msg.saveChanges();
         		tr.sendMessage(msg, msg.getAllRecipients());
         		tr.close();
         		confirmation[i] = "SUCCESS: Message sent to " + to[i];
        	} catch (Exception e) {
        	 	//e.printStackTrace();
        	 	confirmation[i] = "FAIL: Message not sent to " + to[i];
         	}
         }
        return confirmation;
    }
} 


