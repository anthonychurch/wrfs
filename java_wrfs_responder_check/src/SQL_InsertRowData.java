import java.sql.Connection;
import java.sql.DriverManager;
import java.sql.SQLException;
import java.sql.Statement;

public class SQL_InsertRowData {
	//Variable used to solve duplicate primary key error in the SQL insert command
	private static boolean stopTask = false;
	//prints the contents of a String Array
	private static Connection conn = null;
	private static String url = null;
	private static String userName = null;
	private static String password = null;
	
	public SQL_InsertRowData(String DBurl, String DBuserName, String DBpassword){
		url = DBurl;
		userName = DBuserName;
		password = DBpassword;
	}
	
	private static void printStringArray(String[] array){
		for(int i = 0; i < array.length; i++){
			System.out.println("ADVICE :: SQL_InsertRowData : array[i] = " + array[i]);
		}
	}
	//Builds the SQL Insert command as a String to be run through JDBC
	private static String SQLinsertCommand(String prefixTxt, String[] bodyTxtArray, String[] bodyTxtTypeArray, String suffixTxt){
		String returnText = "";
		for(int i = 0; i < bodyTxtArray.length; i++){
			if(bodyTxtTypeArray[i] == "String"){
				returnText = returnText + "'" + bodyTxtArray[i] + "'";
			}else{
				returnText = returnText + bodyTxtArray[i];
			}
		
			if(i < bodyTxtArray.length - 1){
				returnText = returnText + ", ";
			}
		}
		returnText = prefixTxt + returnText + suffixTxt;
		return returnText;
	}
	
	public boolean connect(){

		boolean success = true;
		try {
			conn = DriverManager.getConnection(url, userName, password);
		} catch (SQLException e) {
			// TODO Auto-generated catch block
			//printStackTrace();
			success = false;
		}
		System.out.println("ADVICE :: SQL_InsertRowData : conn = " + conn);
		return success;
	}
	
	public void close(){
		try {
			conn.close();
		} catch (SQLException e) {
			// TODO Auto-generated catch block
			e.printStackTrace();
		}
	}
	
	//public void Insert(String url, String userName, String password, int code, String name, String rank, String experience, String timeStamp){
	public void insert(String tableName, String columnName, String[] dataTypeArray, String[] dataArray){
		//Connection conn = null;
		//Variable used to solve duplicate primary key error in the SQL insert command
		int maxValue = 0;
		stopTask = false;
		//Variable used to solve an inifinite while loop error
		int loopSafetyTrigger = 0;
		String ist = "";
		String suffix = ")";
		String prefix = "INSERT INTO " + tableName + " VALUES (";
		//Create Insert Statement
		while(stopTask == false){
			ist = SQLinsertCommand(prefix, dataArray, dataTypeArray, suffix);
			System.out.println("ADVICE :: SQL_InsertRowData : ist = " + ist);
			try {
				//Connect to Data Base
				//conn = DriverManager.getConnection(url, userName, password);
				//System.out.println("ADVICE :: SQL_InsertRowData : conn = " + conn);
				Statement st = conn.createStatement();
				st.executeUpdate(ist);
				//conn.close(); 
				if(loopSafetyTrigger > 10){
					stopTask = true;
					System.out.println("ERROR :: SQL_InsertRowData : loopSafetyTrigger initiated");
					System.out.println("ADVICE :: SQL_InsertRowData : stopTask = " + stopTask);
				}
				loopSafetyTrigger += 1;
			} catch (Exception e) { 
				//System.err.println("Got an exception! "); 
				System.err.println(e.getMessage()); 
				String exception = e.getMessage();
				//System.out.println("ADVICE :: SQL_InsertRowData : exception = " + exception);
				String subPrefixError = exception.substring(0,15);
				//System.out.println("ADVICE :: SQL_InsertRowData : subPrefixError = " + subPrefixError);
				String subSuffixError = exception.substring(exception.length()-18,exception.length());
				//System.out.println("ADVICE :: SQL_InsertRowData : subSuffixError = " + subSuffixError);
				String error = subPrefixError + subSuffixError;
				//System.out.println("ADVICE :: SQL_InsertRowData : error = " + error);
				int compare = error.compareToIgnoreCase("Duplicate entry for key 'PRIMARY'");
				//System.out.println("ADVICE :: SQL_InsertRowData : compare = " + compare);
				if( compare == 0){
					System.out.println("ERROR :: SQL_InsertRowData : exception = " + exception);
					System.out.println("ERROR :: SQL_InsertRowData : fixing exception, getting the next highest column value in primary key column " + columnName);
					maxValue = SQL_GetColumnValue.getMaxValue(url,userName,password,tableName,columnName);
					maxValue += 1;
					System.out.println("ERROR :: SQL_InsertRowData : maxValue = " + maxValue);
					dataArray[0] = Integer.toString(maxValue);
					printStringArray(dataArray);
				}
			}
			stopTask = true;
			System.out.println("ADVICE :: SQL_InsertRowData : stopTask = " + stopTask);
        } 
	}
}
