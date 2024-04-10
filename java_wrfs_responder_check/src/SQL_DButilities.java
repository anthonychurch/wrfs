import java.sql.Connection;
import java.sql.DriverManager;
import java.sql.ResultSet;
import java.sql.SQLException;
import java.sql.Statement;


public class SQL_DButilities {
	private static String url;// = "jdbc:mysql://localhost:3306/winRfs_availibility";
	private static String userName;// = "root";
	private static String password;// = "please";
	private static Connection conn = null;
	private static Statement st = null;
	
	public void setUserPassUrl(String DBadress, String user, String pw){
		url = DBadress;
		userName = user;
		password = pw;
	}
	
	public String[] connect(){
		String result[] = {"true","Is connected"};
		try {
			conn = DriverManager.getConnection(url, userName, password);
		} catch (SQLException e) {
			// TODO Auto-generated catch block
			e.printStackTrace();
			result[0] = "false";
			result[1] = e.toString();
		}
		return result;
	}
	
	public void close(){
		try {
			conn.close();
		} catch (SQLException e) {
			// TODO Auto-generated catch block
			e.printStackTrace();
		}
	}
	
	public ResultSet query(String statement){
		ResultSet rs = null;
		try {
			st = conn.createStatement();
			rs = st.executeQuery(statement);
		} catch (SQLException e) {
			// TODO Auto-generated catch block
			e.printStackTrace();
		}
		return rs;
	}
	
	private static String insertCommand(String prefixTxt, String[] bodyTxtArray, String[] bodyTxtTypeArray, String suffixTxt){
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
	
	public String[] executeUpdate(String ist){
		String[] result = {"true", "statement inserted"};
		try {
			st.executeUpdate(ist);
		} catch (SQLException e) {
			// TODO Auto-generated catch block
			e.printStackTrace();
			result[0] = "false";
			result[1] = e.toString();
		}
		return result;
	}
	
	private static String[] convertResultSetToArray(ResultSet r) throws SQLException{
		String[] array = {r.getString(1),r.getString(2),r.getString(3),r.getString(4),r.getString(5),r.getString(6)};
		return array;
	}
	
	public int getMaxValue(String tableName, String columnName){
		int maxValue= 0;//-1;
		try {
			String statementMax = "select " + columnName + " from " + tableName + " where " + columnName + " =(select max(" + columnName +") from " + tableName + ")";
			System.out.println("ADVICE :: SQL_DButilities.getMaxValue : statementMax = " + statementMax);
			Statement st = conn.createStatement();
			ResultSet rsMax = st.executeQuery(statementMax);
		
			while(rsMax.next()) {
				maxValue = rsMax.getInt(1);
				System.out.println("ADVICE :: SQL_DButilities.getMaxValue : rs = " + rsMax.getInt(1));
			}
		} catch (Exception e) {
			e.printStackTrace();
			System.out.println("ERROR :: SQL_DButilities.getMaxValue : Exception: " + e.getMessage());
		} 
		return maxValue;
	}
	
	public static String[][] getTimeStampRange( String tableName, String columnName, String timeStamp1, String timeStamp2){
		int i = 0;
		String[][] returnArray = null;
		String statement = "select * from " + tableName + " where " + columnName + " between '" + timeStamp1 + "' and '" + timeStamp2 + "'";
		System.out.println("ADVICE :: SQL_DButilities.getTimeStampRange : statement = " + statement);
		try {
			conn = DriverManager.getConnection(url, "root","please");
			System.out.println("ADVICE :: SQL_DButilities.getTimeStampRange : conn = " + conn);
			Statement st = conn.createStatement();
			ResultSet rs = st.executeQuery(statement);
			//RE_QUERY AND STORE STATEMENT DUE TO THE LIMITATIONS OF THE WHILE STATEMENTS
			Statement st2 = conn.createStatement();
			ResultSet rs2 = st2.executeQuery(statement);
			//CALCULATE SIZE OF THE RESULT SET; i.e. how many rows
			while (rs.next()) {
				i += 1;
			}
			String[][] temp = new String[i][6];
			i = 0;
			while (rs2.next()) {
				temp[i] = convertResultSetToArray(rs2);
				i += 1;
			}
			returnArray = temp;
			temp = null;
		} catch (Exception e) {
			e.printStackTrace();
			System.out.println("ERROR :: SQL_DButilities.getTimeStampRange : Exception: " + e.getMessage());
		} 
		return returnArray;
	}
	
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

	public void insert(String tableName, String columnName, String[] dataTypeArray, String[] dataArray,int maxValue){
		boolean stopTask = false;
		//Variable used to solve duplicate primary key error in the SQL insert command
		//int maxValue = 0;
		//Variable used to solve an inifinite while loop error
		int loopSafetyTrigger = 0;
		stopTask = false;
		String ist = "";
		String suffix = ")";
		String prefix = "INSERT INTO " + tableName + " VALUES (";
		//Create Insert Statement
		while(stopTask == false){
			ist = SQLinsertCommand(prefix, dataArray, dataTypeArray, suffix);
			System.out.println("ADVICE :: SQL_DButilities.ist = " + ist);
			try {
				Statement st = conn.createStatement();
				st.executeUpdate(ist);
				//conn.close(); 
				if(loopSafetyTrigger > 10){
					stopTask = true;
					System.out.println("ERROR :: SQL_DButilities.loopSafetyTrigger initiated");
					System.out.println("ADVICE :: SQL_DButilities.stopTask = " + stopTask);
				}
				loopSafetyTrigger += 1;
			} catch (Exception e) { 
				System.err.println(e.getMessage()); 
				String exception = e.getMessage();
				String subPrefixError = exception.substring(0,15);
				String subSuffixError = exception.substring(exception.length()-18,exception.length());
				String error = subPrefixError + subSuffixError;
				int compare = error.compareToIgnoreCase("Duplicate entry for key 'PRIMARY'");
				if( compare == 0){
					System.out.println("ERROR :: SQL_DButilities.exception = " + exception);
					System.out.println("ERROR :: SQL_DButilities.fixing exception, getting the next highest column value in primary key column " + columnName);
					maxValue = SQL_GetColumnValue.getMaxValue(url,userName,password,tableName,columnName);
					maxValue += 1;
					System.out.println("ERROR :: SQL_DButilities.maxValue = " + maxValue);
					dataArray[0] = Integer.toString(maxValue);
					printStringArray(dataArray);
				}
			}
			stopTask = true;
			System.out.println("ADVICE :: SQL_DButilities.stopTask = " + stopTask);
        } 
	}

	public static void printStringArray(String[] array){
		for(int i = 0; i < array.length; i++){
			System.out.println("ADVICE :: SQL_DButilities : array[i] = " + array[i]);
		}
	}

	public void printOutput(ResultSet r) throws SQLException{
		System.out.println("id: " + r.getInt(1));
		System.out.println("code: " + r.getInt(2));
		System.out.println("name: " + r.getString(3));
		System.out.println("rank: " + r.getString(4));
		System.out.println("experience: " + r.getString(5));
		System.out.println("timeStamp: " + r.getString(6));
	}
}
