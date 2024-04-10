import java.io.File;
import java.text.Format;
import java.text.SimpleDateFormat;
import java.util.Date;
import java.util.Properties;

import javax.activation.DataHandler;
import javax.activation.FileDataSource;
import javax.mail.Address;
import javax.mail.FetchProfile;
import javax.mail.Flags;
import javax.mail.Folder;
import javax.mail.Message;
import javax.mail.MessagingException;
import javax.mail.NoSuchProviderException;
import javax.mail.PasswordAuthentication;
import javax.mail.Session;
import javax.mail.Store;
import javax.mail.Transport;
//import javax.mail.URLName;
import javax.mail.internet.AddressException;
import javax.mail.internet.InternetAddress;
import javax.mail.internet.MimeMessage;
import javax.mail.search.FlagTerm;


public class Gmail_Imap_Utilities {
    private static Store store = null;
    private String username, password;
    private Folder folder;
    
    public void setUserPass(String username, String password) {
        this.username = username;
        this.password = password;
    }
    
    public boolean connect(){
    	boolean success = true;
       	Properties props = System.getProperties();
		props.setProperty("mail.store.protocol", "imaps");
		Session session = Session.getDefaultInstance(props, null);
		try {
			store = session.getStore("imaps");
			store.connect("imap.gmail.com", username, password);
		} catch (MessagingException e) {
			// TODO Auto-generated catch block
			//e.printStackTrace();
			success = false;
		}
		return success;
    }
    
    public void close() throws Exception {
		store.close();
    }
    
    public Message[] getNewMessages() throws MessagingException{
    	FlagTerm ft = new FlagTerm(new Flags(Flags.Flag.SEEN), false);
		Message[] messages = folder.search(ft);
    	return messages;
    }
    
    public void sendMessage(String sender, String recipient, String subject, String body){
    	Properties props = new Properties();
		props.put("mail.smtp.auth", "true");
		props.put("mail.smtp.starttls.enable", "true");
		props.put("mail.smtp.host", "smtp.gmail.com");
		props.put("mail.smtp.port", "587");

		Session session = Session.getInstance(props,
		  new javax.mail.Authenticator() {
			protected PasswordAuthentication getPasswordAuthentication() {
				return new PasswordAuthentication(username, password);
			}
		  });

		try {

			Message message = new MimeMessage(session);
			message.setFrom(new InternetAddress(sender));
			message.setRecipients(Message.RecipientType.TO,InternetAddress.parse(recipient));
			message.setSubject(subject);
			message.setText(body);

			Transport.send(message);

			//System.out.println("Done");

		} catch (MessagingException e) {
			//throw new RuntimeException(e);
		}
		props = null;
    }
    
    public void sendMessageAttachment(String sender, String recipient, String subject, String body, String attachment){
    	Properties props = new Properties();
		props.put("mail.smtp.auth", "true");
		props.put("mail.smtp.starttls.enable", "true");
		props.put("mail.smtp.host", "smtp.gmail.com");
		props.put("mail.smtp.port", "587");

		Session session = Session.getInstance(props,
		  new javax.mail.Authenticator() {
			protected PasswordAuthentication getPasswordAuthentication() {
				return new PasswordAuthentication(username, password);
			}
		  });

		try {

			Message message = new MimeMessage(session);
			message.setFrom(new InternetAddress(sender));
			message.setRecipients(Message.RecipientType.TO,InternetAddress.parse(recipient));
			message.setSubject(subject);
			message.setText(body);
			
			FileDataSource fds = new FileDataSource(attachment);
			message.setDataHandler(new DataHandler(fds));
			message.setFileName(fds.getName());

			message.setSentDate(new Date());
			
			Transport.send(message);

			//System.out.println("Done");

		} catch (MessagingException e) {
			//throw new RuntimeException(e);
		}
		props = null;
    }
    
    public void openFolder(String name) throws Exception {
    	folder = store.getFolder(name);
    	folder.open(Folder.READ_WRITE);
    }
    
    public void closeFolder() throws Exception {
    	folder.close(true);
    }
    
	protected String[] createFolder(String folderName){   
	    String msg = null;
	    String[] returnArray = new String[2];
	    try  
	    {   
	    	Folder defaultFolder = store.getDefaultFolder();
	    	Folder newFolder = defaultFolder.getFolder(folderName);  
	        //System.out.println("newFolder: " + newFolder);
	        if (!newFolder.exists()){
	        	if(newFolder.create(Folder.HOLDS_MESSAGES)){
	        		msg = "ADVICE :: Gmail_Imap_Utilities : Created folder " + newFolder;
		        	//System.out.println(msg); 
	        		returnArray[0] = "true";
	        		returnArray[1] = msg;
	        	}else{
	        		msg = "ADVICE :: Gmail_Imap_Utilities : Did not create folder " + newFolder;
	        		returnArray[0] = "false";
	        		returnArray[1] = msg;
	        	}
	        }else{
	        	msg ="ADVICE :: Gmail_Imap_Utilities : " + newFolder + " already exists";
	        	//System.out.println(msg );
        		returnArray[0] = "true";
        		returnArray[1] = msg;
	        }

	    } catch (Exception e)   
	    {   
	    	msg = "ADVICE :: Gmail_Imap_Utilities : Error creating folder: " + e.getMessage();
	        //System.out.println(msg);   
	        e.printStackTrace();   
	        //isCreated = false;   
    		returnArray[0] = "false";
    		returnArray[1] = msg;
	    }   
	    return returnArray;   
	}
    
    public void setMessageFlag(Message msg, Flags f) throws Exception {
    	//msg.setFlags(new Flags(Flags.Flag.SEEN),true);
    	msg.setFlags(f,true);
    }
    
    public Address[] getSender(Message m) throws MessagingException{
    	Address[] sender = m.getFrom();
    	return sender;
    }
    
    public Address[] getRecipientTO(Message m) throws MessagingException{
    	Address[] to = m.getRecipients(Message.RecipientType.TO);
    	return to;
    }
    
	public void moveMessages(Message[] msgs, String src, String dest) throws MessagingException{
    	if (msgs.length != 0) {
    		folder.copyMessages(msgs, store.getFolder(dest));
    		folder.setFlags(msgs, new Flags(Flags.Flag.DELETED), true);
    	}
    }
	
    public void printMessagesEnvelopes( Message[] msgs, String tag ) throws Exception {
        
        // Use a suitable FetchProfile
        FetchProfile fp = new FetchProfile();
        fp.add(FetchProfile.Item.ENVELOPE);        
        folder.fetch(msgs, fp);
        
        for (int i = 0; i < msgs.length; i++) {
        	try{
            	//System.out.println("--------------------------");
        		//System.out.println(tag + " #" + (i + 1) + ":");
        		dumpEnvelope(msgs[i]);
        		//retreiveSubject(msgs[i]);
        	} catch (AddressException ad){
        		//System.out.println("MESSAGE #" + (i + 1) + ": Failed due to wrongly formatted address");
        	}
        }
     }
    
    public String[] retreiveSubject(Message m) throws Exception {   
    	//SUBJECT
        String subject = m.getSubject();
        //pr("ADVICE :: Gmail_Imap_Utilities : SUBJECT = " + subject);
        // DATE - TIMSTAMP FORMAT
        Date getDate = m.getSentDate();
        Format formatter = new SimpleDateFormat("yyyy-MM-dd HH:mm:ss");
        String timestamp = formatter.format(getDate);
        //pr("ADVICE :: Gmail_Imap_Utilities : timestamp = " + timestamp);
        String[] subjectOutput = {subject,timestamp,getSender(m)[0].toString()};
        
        return subjectOutput;
    }
    
    public static void dumpEnvelope(Message m) throws Exception {        
        pr(" ");
        Address[] a;
        // FROM
        if ((a = m.getFrom()) != null) {
            for (int j = 0; j < a.length; j++)
                pr("FROM: " + a[j].toString());
        }
        
        // TO
        if ((a = m.getRecipients(Message.RecipientType.TO)) != null) {
            for (int j = 0; j < a.length; j++) {
                pr("TO: " + a[j].toString());                
            }
        }
        
        // SUBJECT
        pr("SUBJECT: " + m.getSubject());
        
        // DATE
        Date d = m.getSentDate();
        pr("SendDate: " +
                (d != null ? d.toString() : "UNKNOWN"));
    }
    
    static String indentStr = "    ";
    static int level = 0;
    
    /**
     * Print a, possibly indented, string.
     */
    public static void pr(String s) {
        
        System.out.print(indentStr.substring(0, level * 2));
        System.out.println(s);
    }
    
}
