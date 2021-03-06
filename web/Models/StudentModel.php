<?php
    // Title: Models/StudentModel.php
    // Author: Joshua Anderson
    // Modified Date: 8/3/13
    // Description: This model handles all the interaction with the database regarding the STUDENT table.  This will 
    // incoorporate the PDOWrapper class when making the calls to insert, update, select, delete student record(s).

    require_once('../Classes/PDOWrapper.php');
    
    if(isset($_POST['action']) && !empty($_POST['action'])) {
        $action = $_POST['action'];
        
        if($action == 'AjaxGenerateStudentList') {
            include('../inc/util.php');
            include('../Controllers/StudentController.php');
            $conn = connect();
            $internshipListing = StudentController::getStudentList(true, $conn);
            echo StudentListView::showMasterStudentListForCreate($internshipListing);
        }
        
        if($action == 'StudentSearch') {
            include('../inc/util.php');
            include('../Views/StudentListView.php');
            $query = $_POST['q'];
            $listing = "";
            $results = StudentModel::searchForStudent($query);
            foreach($results as $record) {
                $listing = $listing . StudentListView::showOneListingForCreate($record['student_id'], $record['first_name'], $record['last_name'], $record['email']);
            }
            
            echo StudentListView::getTopListingHTML(true) . $listing . StudentListView::getBottomListingHTML();
        }

        if($action == 'StudentContinue') {
            include('../Views/CreateFormView.php');
            echo CreateFormView::getCompanyForm();
        }
    }
    
    
    class StudentModel 
    {
        /*
         * This function takes in 3 params:
         * 1. lname = Last Name of Student
         * 2. fname = First Name of Student
         * 3. unh_email = Email of Student
         * 
         * Creates a PDO object, and first checks to see if the student email that was passes already exists, if it does, it 
         * will return that students' ID - if not, this will create an associative array ($info) that contains the params.
         * We will then pass that array, along with the PDO object, and which table we want to use.  
         * 
         * RETURNS ID of student
        **/
        static function createStudent($lname, $fname, $unh_email)
        {
            $pdo = getPDO();
            $inserted_user_id = null;
           
            if(strlen(StudentModel::checkForStudent($unh_email)) > 0) {
                return StudentModel::checkForStudent($unh_email);
            } else {
                $info = array('last_name' => $lname, 'first_name' => $fname, 'email' => $unh_email);
                return PDOWrapperModel::insert($pdo, 'student', $info);
            }
        }
    
        /*
         * This function takes in 1 param:
         * 1. query = The search term
         * This function does a simple query of the database and searches for records that match either the last name or
         * email of the student.
         * 
         * RETURNS an array of the resulting students.
        **/
        static function searchForStudent($query) {
            $pdo = getPDO();
            $result = array();
            if(!empty($query)) {
                $sql = "SELECT * FROM student WHERE LOWER(last_name) LIKE LOWER(?) OR LOWER(email) LIKE LOWER(?)";
                $stmt = $pdo->prepare($sql);
                $stmt->execute(array('%'.$query.'%','%'.$query.'%'));
                return $stmt->fetchAll();
                
            } else {
                return "Please enter a search query!";
            }
        }
       
        /*
         * This function takes in 1 param:
         * 1. unh_email = The email of the student in question
         * This function does a simple check of the student table to see if there is a record with the email address
         * provdied.
         * 
         * RETURNS the ID if a match is made.
        **/
        static function checkForStudent($unh_email) {
            $pdo = getPDO();
            $sql = "SELECT student_id FROM student where email=:email";
           
            if($stmt = $pdo->prepare($sql)) {
                $stmt->bindParam(':email', $unh_email);
                
                $stmt->execute();
                return $stmt->fetchColumn(); 
                //fetchColumn() will get the index of the columns provided.  This case, only one was selected so don't need to pass any
                  
                $stmt->close();
            } 
           
            $conn->close();
        }
       
        // returns an array containing all the student records
        static function selectAllInternships($conn = null) {
            if($conn == null) {
                $conn = connect();
            }        
                
            $internshipsList = array();
           
            $sql = "SELECT * FROM student ORDER BY last_name asc";
            $stmt = $conn->query($sql);
           
            while($row = $stmt->fetch_assoc()) {
                $internshipsList[] = $row;
            }
            $stmt->close();
            return $internshipsList;
        }
     }


?>