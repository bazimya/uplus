<?php
// START INITIATE
	include ("db.php");
	// var_dump($_SERVER["REQUEST_METHOD"]);
	if ($_SERVER["REQUEST_METHOD"] == "POST") 
	{
		if(isset($_POST['action']))
		{
			$_POST['action']();
		}
		else
		{
			echo 'Please read the API documentation';
		}
	}
	else
	{
		echo 'UPLUS API V02';
	}
// END INITIATE

// START FORUMS
	function listForums()
	{
		require('db.php');
		$memberId		= mysqli_real_escape_string($db, $_POST['memberId']);
		$query = $investDb->query("SELECT F.id forumId, F.title, F.subtitle, F.icon, IFNULL((SELECT M.mine FROM forummember M WHERE M.memberId = '$memberId' AND M.forumId = F.id),'YES') AS mine  FROM forums F WHERE archive <> 'YES'")or die(mysqli_error($investDb));
		$forums = array();
		while ($forum = mysqli_fetch_array($query))
		{
			if($forum['mine'] == 'YES')
			{
				$joined = '0';
			}
			else
			{
				$joined = '1';
			}
			$forumId = $forum['forumId'];
			
			$countQuery = $investDb->query("SELECT * FROM forummember WHERE forumId = '$forumId' AND mine = 'NO'")or die(mysqli_error($investDb));
		   	$joinedCount = mysqli_num_rows($countQuery);
		    $forums[] = array(
				"forumId"		=> $forumId,
				"forumTitle"	=> $forum['title'],
				"forumSubtitle"	=> $forum['subtitle'],
				"forumIcon"		=> $forum['icon'],
				"joined"		=> $joined,
				"joinedCount"	=> $joinedCount
			);
		}
		header('Content-Type: application/json');
		$forums = json_encode($forums);
		echo $forums;
	}

	function joinForum()
	{
		require('db.php');
		$memberId	= mysqli_real_escape_string($db, $_POST['memberId']);
		$forumId	= mysqli_real_escape_string($db, $_POST['forumId']);
		if(mysqli_num_rows($investDb->query("SELECT * FROM forumuser WHERE forumCode = '$forumId' AND (userCode = '$memberId' AND archive <> 'YES')"))>0)
		{
			echo "User Already In with memberId (".$memberId.") And forumId: (".$forumId.")";
		}
		elseif (mysqli_num_rows($investDb->query("SELECT * FROM forumuser WHERE forumCode = '$forumId' AND (userCode = '$memberId' AND archive = 'YES')"))>0) {
			$query 		= $investDb->query("UPDATE forumuser SET archive = 'NO' WHERE forumCode = '$forumId' and userCode = '$memberId'")or die(mysqli_error($investDb));
			echo "User Brought back in, with memberId (".$memberId.") And forumId: (".$forumId.")";
		}
		else
		{
			$query 		= $investDb->query("INSERT INTO forumuser (forumCode, userCode, createdBy) VALUES ('$forumId','$memberId','$memberId')")or die(mysqli_error($investDb));
			echo "Done with memberId (".$memberId.") And forumId: (".$forumId.")";
		}
		
	}

	function exitForum()
	{
		require('db.php');
		$memberId	= mysqli_real_escape_string($db, $_POST['memberId']);
		$forumId	= mysqli_real_escape_string($db, $_POST['forumId']);
		if(mysqli_num_rows($investDb->query("SELECT * FROM forumuser WHERE forumCode = '$forumId' AND userCode = '$memberId'"))>0)
		{
			$query 		= $investDb->query("UPDATE forumuser SET archive = 'YES' WHERE forumCode = '$forumId' and userCode = '$memberId'")or die(mysqli_error($investDb));
			echo "Done user exited the forum with memberId (".$memberId.") And forumId: (".$forumId.")";
		}
		else
		{
			echo "User wasent in the forum With memberId: (".$memberId.") And forumId: (".$forumId.")";
		}
	}

	function loopFeeds()
	{
		require('db.php');
		$sql = $investDb->query("SELECT F.id feedId, F.feedForumId, F.feedTitle, U.name feedBy, U.userImage feedByImg,
		 F.feedLikes, F.feedComents, F.createdDate feedDate,F.feedContent FROM investments.feeds F INNER JOIN uplus.users U ON F.createdBy = U.id")or die(mysqli_error($investDb));
		$feeds = array();
		while ($row = mysqli_fetch_array($sql))
		{
			$feeds[] = array(
				"feedId"		=> $row['feedId'],
				"feedForumId"	=> $row['feedForumId'],
				"feedTitle"		=> $row['feedTitle'],
				"feedBy"		=> $row['feedBy'],
				"feedByImg"		=> $row['feedByImg'],
				"feedLikes"		=> $row['feedLikes'],
				"feedLikeStatus"=> 'NO', "feedComments" => "12",
				"feedDate"		=> $row['feedDate'],
				"feedContent"	=> $row['feedContent'],
				"feedDate"		=> $row['feedDate']
			);
		}
		foreach ($feeds as $i => $feed) 
		{
			$feedId 	= $feed['feedId'];
			$images 	= array();
            $sql 		= $investDb->query("SELECT imgUrl FROM investmentimg")or die (mysqli_error($investDb));
            while($rowImage = mysqli_fetch_array($sql))
            {
                $images[]  = array(
                    "imgUrl"         => $rowImage['imgUrl']
                );
            }
            $feeds[$i]['feedImage'] = $images;
		}
		
        mysqli_close($db);
        mysqli_close($eventDb);
        header('Content-Type: application/json');
		$feeds = json_encode($feeds);
		echo $feeds;
	}

// END FORUMS
?>
