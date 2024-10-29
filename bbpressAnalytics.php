<?php
/*
Plugin Name: bbPress Analytics
Description: Show some analytics regarding forum like top posters, number of posts per day and more
Version: 0.1
Author: Roni Cohen - www.ronileco.com

/*  Copyright 2012 Roni Cohen (email: ronileco@gmail.com)

    This program is free software; you can redistribute it and/or modify
    it under the terms of the GNU General Public License, version 2, as 
    published by the Free Software Foundation.

    This program is distributed in the hope that it will be useful,
    but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
    GNU General Public License for more details.
*/

/**
/* BBPress Analytics plugin to get some analytics for your BBPress
**/
class bbpressAnalytics{

	/**
	/* Shows the number of posts per day
	**/
	function showPostsPerDay(){
		bbpressAnalytics::printBeforeTable("Posts last 30", "Date", "Posts");
	
		$topicsPerDay = bbpressAnalytics::getTopicsAndRepliesPerDay();
		$totalTopics = 0;
		
		foreach ($topicsPerDay as $topic)
		{
			$totalTopics += $topic['count(id)'];
			$timestamp = strtotime($topic['post_date']);
			
			echo '<tr><td>' . date("d-m-y", $timestamp)  . '</td><td class="n">' . $topic['count(id)'] . '</td>';
		}
		
		bbpressAnalytics::printAfterTable($totalTopics);
		
	}
	
	/**
	/* Shows the number of posts per day
	**/
	function showOrphanPosts(){
		bbpressAnalytics::printBeforeTable("Orphan posts", "post Id", "date");
		
			$orphanPosts = bbpressAnalytics::getOrphanPosts();
			$totalTopics = 0;
			
			foreach ($orphanPosts as $orphanPost)
			{
				$totalTopics += 1;
				
				$post_Id = $orphanPost['ID'];
				$timestamp = strtotime($orphanPost['post_date']);

				echo "<tr><td><a href=/?p=$post_Id>" .$post_Id . '</a></td><td class="n">' . date("d-m-y", $timestamp)  . '</td>';
			}
									
		bbpressAnalytics::printAfterTable($totalTopics);
	}

	
	/**
	/* Shows the number of posts per day
	**/
	function showTopPosters(){
		bbpressAnalytics::printBeforeTable("Top Posters", "Author Id", "Posts");
		
			$topPosters = bbpressAnalytics::getTopPosters();
			$totalTopics = 0;
			
			foreach ($topPosters as $topPoster)
			{
				$totalTopics += $topPoster['count(id)'];
				$author_Id = $topPoster['post_author'];
				
				echo "<tr><td><a href=/?bbp_user=$author_Id>" .$author_Id  . '</a></td><td class="n">' . $topPoster['count(id)'] . '</td>';
			}
									
		bbpressAnalytics::printAfterTable($totalTopics);
	}

	/**
	/* Creates and shows the analytics page on the Dashboard
	**/
	function showAnalytics(){
		
		?>
		
		<div style="margin-left: 45px; margin-top:25px;"> 
		
			<?php
				echo '<span> Num of total topics on the forum: ' . bbpressAnalytics::getTotalTopics() . '</span><br/>';
				echo '<span> Num of total replies on the forum: ' . bbpressAnalytics::getTotalReplies() . '</span><br/></br>';
		
				bbpressAnalytics::showPostsPerDay();
				bbpressAnalytics::showTopPosters();
				bbpressAnalytics::showOrphanPosts();

			?>
			
		</div>			
	<?php
	}
	
	/**
	* Return the orphan posts (posts with no reply)
	**/
	function getOrphanPosts()
	{
		global  $wpdb;
	
		// find list of events
		$qry = " SELECT parentTable.ID, parentTable.post_date FROM wp_posts as parentTable WHERE parentTable.post_type = 'topic' and parentTable.post_status = 'publish' and NOT EXISTS (SELECT * FROM wp_posts as childTable WHERE childTable.post_parent = parentTable.ID ) order by post_date desc;";
		
		$rows = $wpdb->get_results( $qry, 'ARRAY_A');
		
		return $rows;
	}
	
	/**
	* Return the top 10 posters (topics + replies).
	**/
	function getTopPosters()
	{
		global  $wpdb;
		$numOfPosters = 30;
		
		// find list of events
		$qry = " select count(id), post_author from wp_posts where post_type='topic' OR post_type='reply' group by post_author order by count(id) desc limit $numOfPosters;";
		$rows = $wpdb->get_results( $qry, 'ARRAY_A');
		
		return $rows;
	}
	
	/**
	* Return the total number of topics written on the BBPress.
	**/
	function getTopicsAndRepliesPerDay()
	{
		global  $wpdb;

		$numOfDays = 30;
		
		// Get all posts by day
		$qry = "SELECT count(id), (post_date) FROM wp_posts where post_type = 'topic' OR post_type = 'reply' group by DAYOFYEAR(post_date) order by DAYOFYEAR(post_date) desc limit $numOfDays;";
		$rows = $wpdb->get_results( $qry, 'ARRAY_A'  );
		
		return $rows;
	}
	
	/**
	* Return the total number of topics written on the BBPress.
	**/
	function getTotalTopics()
	{
		global  $wpdb;

		// find list of events
		$qry = "SELECT COUNT(*) FROM wp_posts where post_type = 'topic';";
		$events = $wpdb->get_var( $qry );
		
		return $events;
	}
	
	/**
	* Return the total number of replies written on the BBPress.
	**/
	function getTotalReplies()
	{
		global  $wpdb;

		  // find list of events
		$qry = "SELECT COUNT(*) FROM wp_posts where post_type = 'reply';";
		$events = $wpdb->get_var( $qry );
		
		return $events;
	}
	

	/**
	/* Checks whether bbPress is active because we need it. If bbPress isn't active, we are going to disable ourself
	**/
	function on_bbpressPiwikAnalyticsActivation()
	{
		if(!class_exists('bbPress'))
		{
			deactivate_plugins(plugin_basename(__FILE__));
			wp_die( __('Sorry, you need to activate bbPress first.', 'bpress_changeSubscriptionEmail'));
		}
	}
		
	/**
	* Prints the HTML before the columns to create the table
	**/
	function printBeforeTable($tableName, $column1Name, $column2Name)
	{
		?>
		<div style="height: auto; width: 257px; display: table; float:left; margin-left: 20px;">
			<div id="wp-piwik_stats-contentbox-1" class="postbox ">
				<div class="handlediv" title="Click to toggle"><br></div>
				<h3 class="hndle" style="text-align:center;"><span><?php echo $tableName ?><span></h3>
				<div class="inside">
					<div class="wp-piwik-graph-wide">
						<div id="wp-piwik_stats_vistors_graph" style="" class="jqplot-target"></div>
					</div>

					<div class="table">
						<table class="widefat wp-piwik-table">
							<thead>
								<tr>
									<th><?php echo $column1Name ?> </th>
									<th class="n"> <?php echo $column2Name ?></th>
								</tr>
							</thead>
					<!-- <tbody style="cursor:pointer;"> -->
							<tbody style="">
	<?php
	}
	
	/**
	* Prints the HTML after the columns to close the table
	**/
	function printAfterTable($totalTopics)
	{
	?>
									<tr><td class="n" colspan="4"><strong>Total Posts</strong> <?php echo $totalTopics ?> </td></tr>		
							</tbody>
						</table>
					</div>
				</div>
			</div>
		</div>
<?php
	}
}

/* Register hooks, filters and actions */

// On plugin activation, check whether bbPress is active
register_activation_hook(__FILE__, 'on_changeEmailSubscribeEmailActivation');

add_action('admin_menu', 'bbpress_analytics_menu');

function bbpress_analytics_menu() {
	add_dashboard_page('BBpress Analytics Dashboard', 'BBpress Analytics', 'read', 'BbpressAnalytics', 'bbpressAnalytics::showAnalytics');
}

?>
