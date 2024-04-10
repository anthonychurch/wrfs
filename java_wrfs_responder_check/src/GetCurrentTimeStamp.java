 
import java.sql.Timestamp;
 
public class GetCurrentTimeStamp{
	public Timestamp get(){
		java.util.Date date= new java.util.Date();
		return new Timestamp(date.getTime());
	} 
}