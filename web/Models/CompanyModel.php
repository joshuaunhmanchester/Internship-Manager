<?php

    if(isset($_POST['action']) && !empty($_POST['action'])) {
        $action = $_POST['action'];
        
        if($action == 'AjaxGenerateCompanyList') {
            include('../inc/util.php');
            include('../Controllers/CompanyController.php');
            $conn = connect();
            $internshipListing = CompanyController::getCompanyListing(true, $conn);
            echo CompanyListView::showMasterCompanyListForCreate($internshipListing);
        }
        
        if($action == 'CompanySearch') {
            include('../inc/util.php');
            include('../Views/CompanyListView.php');
            $pdo = getPDO();
            $query = $_POST['q'];
            $listing = "";
            $results = CompanyModel::searchForCompany($query, $pdo);
            foreach($results as $record) {
                $listing = $listing . CompanyListView::showOneListingForCreate($record['company_id'], $record['name'], $record['website_url'], $record['city'], $record['state']);
            }
            
            echo CompanyListView::getTopListingHTML(true) . $listing . CompanyListView::getBottomListingHTML();
        }

        if($action == 'CompanyContinue') {
            include('../Views/CreateFormView.php');
            echo CreateFormView::getSupervisorForm();
        }
    }
    
    
    class CompanyModel  {
        // before you insert, check the unique constraints of the table: unh_email has to be unique.
        // so first do a select query and select the ID so if there is a result row, return that ID
        static function createCompany($cName, $cWebURL, $cCity, $cState) {
            $conn = connect();
            $inserted_company_id = null;
           
            $sql = "INSERT INTO company (name, website_url, city, state) VALUES (?, ?, ?, ?)";
        
            if(!$stmt = $conn->prepare($sql)) {
                exit("You have a MySQL syntax error: " . $sql . "<br />
                    Execution failed: " . $stmt->errno . ": " . $stmt->error);
            }
           
            if(!$stmt->bind_param("ssss", $cName, $cWebURL, $cCity, $cState)) {
                exit("Your arguments do not match the table columns.");
            }
           
            if(!$stmt->execute()) {
                exit("Execution failed. " . $stmt->errno . ": " . $stmt->error);
            }
           
            mysqli_close($conn);
            exit();
           
            return $inserted_company_id = $conn->insert_id;
        }
    
        static function searchForCompany($query, $pdo) {
            $result = array();
            if(!empty($query)) {
                $sql = "SELECT * FROM company WHERE LOWER(name) LIKE LOWER(?)";
                $stmt = $pdo->prepare($sql);
                $stmt->execute(array('%'.$query.'%'));
                return $stmt->fetchAll();
                
            } else {
                return "Please enter a search query!";
            }
        }
       
        static function checkForCompany($conn, $unh_email) {
            // not being used right now
            $sql = "SELECT student_id FROM student where email=?";
           
            if($stmt = $conn->prepare($sql)) {
                $stmt->bind_param("s", $unh_email);
                
                $stmt->execute();
                $stmt->bind_result($out_id);
                $stmt->fetch();
               
                return $out_id;
                  
                $stmt->close();
            } 
           
            $conn->close();
        }
       
        // returns an array containing all the student records
        static function selectAllCompanies($conn = null) {
            if($conn == null) {
                $conn = connect();
            }        
                
            $companyList = array();
           
            $sql = "SELECT * FROM company ORDER BY name asc";
            $stmt = $conn->query($sql);
           
            while($row = $stmt->fetch_assoc()) {
                $companyList[] = $row;
            }
            $stmt->close();
            return $companyList;
        }
     }


?>