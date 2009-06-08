<?php
/*
Plugin Name: Wordpress MU Demo Data Creator
Plugin URI: http://www.stillbreathing.co.uk/projects/mu-demodata/
Description: A plugin for Wordpress MU which will allow a site administrator to create multiple fake users and blogs to test their WPMU installation. This is very useful when developing plugins and themes that you need to work with many users and blogs.
Version: 0.1
Author: Chris Taylor
Author URI: http://www.stillbreathing.co.uk
*/

// when the admin menu is built
add_action('admin_menu', 'demodata_add_menu_items');

// add the menu items to the Site Admin list
function demodata_add_menu_items()
{
	if (is_site_admin())
	{
		add_action("admin_head", "demodata_css");
		add_submenu_page('wpmu-admin.php', 'Demo Data Creator', 'Demo Data Creator', 10, 'demodata_form', 'demodata_form');
	}
}

// add the CSS to the admin page head
function demodata_css()
{
	if (isset($_GET["page"]) && $_GET["page"] == "demodata_form")
	{
		echo '
		<style type="text/css">
		html body form.demodata fieldset p {
		border-width: 0 0 1px 0;
		border-color: #AAA;
		}
		html body form.demodata fieldset label {
		float: left;
		width: 32em;
		}
		html body form.demodata fieldset input {
		width: 6em;
		}
		html body form.demodata fieldset input.text {
		width: 18em;
		}
		html body div.demodatasuccess {
		padding: 0 1em 1em 1em;
		margin-top: 1em;
		background: #D2FFCF;
		border: 1px solid #188F11;
		}
		html body div.demodataerror {
		padding: 0 1em 1em 1em;
		margin-top: 1em;
		background: #FFCFCF;
		border: 1px solid #BF0B0B;
		}
		</style>
		';
	}
}

// create demo data
function demodata_create()
{
	global $wpdb;
	global $current_site;

	// get the users settings
	$users = @$_POST["users"] == "" ? 100 : (int)$_POST["users"];
	$users = $users > 1000 ? $users = 1000 : $users = $users;
	$usernametemplate = @$_POST["usernametemplate"] == "" ? "Demo user [x]" : $_POST["usernametemplate"];
	if (strpos($usernametemplate, "[x]") === false){ $usernametemplate .= " [x]"; }
	$useremailtemplate = @$_POST["useremailtemplate"] == "" ? "demouser[x]@" . $current_site->domain : $_POST["useremailtemplate"];		
	$membershiptype = @$_POST["membershiptype"] == "" ? 1 : (int)$_POST["membershiptype"];
	
	// get the blog settings
	$maxblogsperuser = @$_POST["maxblogsperuser"] == "" ? 3 : (int)$_POST["maxblogsperuser"];
	$maxblogsperuser = $maxblogsperuser > 5 ? $maxblogsperuser = 5 : $maxblogsperuser = $maxblogsperuser;
	$blognametemplate = @$_POST["blognametemplate"] == "" ? "Demo blog [x]" : $_POST["blognametemplate"];	
	
	// get the post settings
	$maxblogposts = @$_POST["maxblogposts"] == "" ? 50 : (int)$_POST["maxblogposts"];
	$maxblogposts = $maxblogposts > 100 ? $maxblogposts = 100 : $maxblogposts = $maxblogposts;
	$maxpostlength = @$_POST["maxpostlength"] == "" ? 10 : (int)$_POST["maxpostlength"];
	$maxpostlength = $maxpostlength > 50 ? $maxpostlength = 50 : $maxpostlength = $maxpostlength;
	$maxpostlength = $maxpostlength < 1 ? $maxpostlength = 1 : $maxpostlength = $maxpostlength;
	
	// get the links settings
	$maxbloglinks = @$_POST["maxbloglinks"] == "" ? 25 : (int)$_POST["maxbloglinks"];
	$maxbloglinks = $maxbloglinks > 100 ? $maxbloglinks = 100 : $maxbloglinks = $maxbloglinks;
	
	// check all the settings
	if ($users != "" &&
		$usernametemplate != "" &&
		$useremailtemplate != "" &&
		$membershiptype != "" && 
		$maxblogsperuser != "" &&
		$blognametemplate != "" &&
		$maxblogposts != "" &&
		$maxpostlength != "" &&
		$maxbloglinks != ""
	)
	{
		$go = true;
	} else {
		$go = false;
	}
	
	// if the settings are OK
	if ($go)
	{
		$userx = 0;
		$blogx = 0;
		$postx = 0;
		
		// loop the number of required users
		for($u = 0; $u < $users; $u++)
		{
			$userx++;
			
			// generate the details for this user
			$username = str_replace("[x]", $userx, $usernametemplate);
			$email = str_replace("[x]", $userx, $useremailtemplate);
			$random_password = substr(md5(uniqid(microtime())), 0, 6);
			
			// check the user can be created
			if ($userid = wp_create_user($username, $random_password, $email) > 0)
			{
			
				// if the membership type is 1 or 2
				if ($membershiptype == 1 || $membershiptype == 2)
				{
					// set the minimum number of blogs
					if ($membershiptype == 1)
					{
						$minblogs = 1;
					} else {
						$minblogs = 0;
					}
					
					// get a random number of blogs
					$blogs = rand($minblogs, $maxblogsperuser);
					
					// loop the number of required blogs
					for($b = 0; $b < $blogs; $b++)
					{
					
						$blogx++;
						
						$blogname = str_replace("[x]", $blogx, $blognametemplate);
						$blogdomain = "demoblog" . $blogx . "." . $current_site->domain;
						
						// check the blog can be created
						if ($blogid = wpmu_create_blog($blogdomain, "/", $blogname, $userid, "", 1))
						{
							// switch to this blog
							switch_to_blog($blogid);
							
							// get a random number of blog posts
							$posts = rand(0, $maxblogposts);
							
							// loop the number of required posts
							for($p = 0; $p < $posts; $p++)
							{
							
								$postx++;
								
								// generate the random post data
								$postcontent = demodata_generate_html(rand(1, $maxpostlength));
								$post = array('post_status' => 'live',
								'post_type' => 'post',
								'post_author' => $userid,
								'ping_status' => 'open',
								'post_parent' => 0,
								'menu_order' => 0,
								'to_ping' => '',
								'pinged' => '',
								'post_password' => '',
								'guid' => 'http://' . $blogdomain . '/post' . $postx,
								'post_content_filtered' => '',
								'post_excerpt' => '',
								'import_id' => 0,
								'post_title' => 'Demo post ' . $postx,
								'post_content' => $postcontent,
								'post_excerpt' => '');
							
								// see if the post can be inserted
								if (!wp_insert_post($post))
								{
									$postx--;
									// break out of this loop
									break;
								}
							
							}
							
						} else {
							$blogx--;
							// break out of this loop
							break;
						}
					
					}
				}
			} else {
				$userx--;
				// break out of this loop
				break;
			}
		}
	
		if ($success)
		{
		
			echo '
			<div class="demodatasuccess">
			<p>' . __("You created the following demo data:") . '</p>
			<ul>
			<li>' . __("Users") . ': ' . $userx . '</li>
			<li>' . __("Blogs") . ': ' . $blogsx . '</li>
			<li>' . __("Posts") . ': ' . $postsx . '</li>
			<li>' . __("Blogroll links") . ': ' . $linksx . '</li>
			</ul>
			<p><a href="http://' . $current_site->domain . '">' . __("Your system is now ready for testing here.") . '</a></p>
			</div>
			';
		} else {
			echo '
			<div class="demodataerror">
			<p>' . __("Sorry, there was an error creating your demo data. The following data was created:") . '</p>
			<ul>
			<li>' . __("Users") . ': ' . $userx . '</li>
			<li>' . __("Blogs") . ': ' . $blogsx . '</li>
			<li>' . __("Posts") . ': ' . $postsx . '</li>
			<li>' . __("Blogroll links") . ': ' . $linksx . '</li>
			</ul>
			</div>
			';
		}
	} else {
	
		echo '
		<div class="demodataerror">
		<p>' . __("Some of your settings were not valid. Please check all the settings below.") . '</p>
		</div>
		';
	
	}
}

// delete demo data
function demodata_delete()
{
	global $wpdb;
	global $current_site;

	echo '
	<p>' . __("Deleting demo data...") . '</p>
	<ul>
	';
	
	$i = 0;
	
	// delete user meta
	$sql = "delete from wp_usermeta where user_id > 1;";
	$users = $wpdb->query($sql);
	
	// count users
	$sql = "select count(id) from wp_users where id > 1;";
	$usercount = $wpdb->get_var($sql);
	
	// delete users
	$sql = "delete from wp_users where id > 1;";
	$users = $wpdb->query($sql);
	
	// alter auto integer
	$sql = "ALTER TABLE wp_users AUTO_INCREMENT = 2;";
	$users = $wpdb->query($sql);

	echo '<li>' . $usercount . ' ' . ("users deleted") . '</li>
	';
	
	$i = 0;
	
	// delete blogs
	$sql = "select blog_id from wp_blogs where blog_id > 1;";
	$blogs = $wpdb->get_results($sql);
	foreach($blogs as $blog)
	{
		if (wpmu_delete_blog($blog->blog_id, true))
		{
			$i++;
		}
	}
	echo '<li>' . $i . ' ' . __("blogs deleted") . '</li>
	';
	
	// alter auto integer
	$sql = "ALTER TABLE wp_blogs AUTO_INCREMENT = 2;";
	$users = $wpdb->query($sql);
	
	$i = 0;
	
	echo '
	</ul>
	';
}

// watch for a form action
function demodata_watch_form()
{
	// if submitting form
	if (is_array($_POST) && count($_POST) > 0 && isset($_POST["action"]))
	{	
		set_time_limit(600);
	
		if ($_POST["action"] == "create")
		{
			demodata_create();
		}
		
		if ($_POST["action"] == "delete")
		{
			demodata_delete();
		}
	}
}

// write out the form
function demodata_form()
{
	global $current_site;

	echo '
	<div class="wrap">
	';
	
	demodata_watch_form();
	
	echo '
	
		<h2>' . __("Create demo data") . '</h2>
		<p>' . __("Use the form below to create multiple test users and blog in this WPMU system. Warning: this may take some time if you are creating a lot of data.") . '</p>
		
		<form action="wpmu-admin.php?page=demodata_form" method="post" class="demodata">
		<fieldset>
		
			<legend>' . __("Users") . '</legend>
			
			<p><label for="users">' . __("Number of users (max 1000)") . '</label>
			<input type="text" name="users" id="users" value="100" /></p>
			
			<p><label for="usernametemplate">' . __("User name template (with [x] for the number)") . '</label>
			<input type="text" name="usernametemplate" id="usernametemplate" value="Demo user [x]" class="text" /></p>
			
			<p><label for="useremailtemplate">' . __("User email template (with [x] for the number)") . '</label>
			<input type="text" name="useremailtemplate" id="useremailtemplate" value="demouser[x]@' . $current_site->domain . '" class="text" /></p>
			
			<p><label for="membershiptype1">' . __("All users must have at least one blog") . '</label>
			<input type="radio" name="membershiptype" id="membershiptype1" value="1" checked="checked" /></p>
			
			<p><label for="membershiptype2">' . __("Users may have zero or more blogs") . '</label>
			<input type="radio" name="membershiptype" id="membershiptype2" value="2" /></p>
			
			<p><label for="membershiptype3">' . __("Only create users, don't create blogs (the blog settings below will be ignored)") . '</label>
			<input type="radio" name="membershiptype" id="membershiptype3" value="3" /></p>
			
		</fieldset>
		<fieldset>
		
			<legend>' . __("Blogs") . '</legend>
			
			<p><label for="maxblogsperuser">' . __("Maximum number of blogs per user (max 5)") . '</label>
			<input type="text" name="maxblogsperuser" id="maxblogsperuser" value="3" /></p>
			
			<p><label for="blognametemplate">' . __("Blog name template (with [x] for the number)") . '</label>
			<input type="text" name="blognametemplate" id="blognametemplate" value="Demo blog [x]" class="text" /></p>
			
			<p><label for="maxblogposts">' . __("Maximum number of posts per blog (max 100)") . '</label>
			<input type="text" name="maxblogposts" id="maxblogposts" value="50" /></p>
			
			<p><label for="maxpostlength">' . __("Maximum number of blog post paragraphs (min 1, max 50)") . '</label>
			<input type="text" name="maxpostlength" id="maxpostlength" value="10" /></p>
			
			<p><label for="maxbloglinks">' . __("Maximum number of links in blogroll (max 100)") . '</label>
			<input type="text" name="maxbloglinks" id="maxbloglinks" value="25" /></p>
			
		</fieldset>

		<fieldset>
		
			<p>' . __("Creating this demo data may take several minutes. Please be patient, and only click the button below once.") . '</p>
			
			<p><label for="create">' . __("Create demo data") . '</label>
			<input type="hidden" name="action" value="create" />
			<button class="button" name="create" id="create">' . __("Create") . '</button></p>
		
		</fieldset>
		</form>
		
		<form action="wpmu-admin.php?page=demodata_form" method="post" class="demodata">
		<fieldset>
		
			<p>Delete all data in your database except for information and tables for blog ID 1 and user ID 1.</p>
		
			<p><label for="delete">Delete demo data</label>
			<input type="hidden" name="action" value="delete" />
			<button class="button" name="delete" id="delete">Delete</button></p>
		
		</fieldset>
		</form>
	</div>
	';
}

// generate random html content
function demodata_generate_html($maxblocks = 4)
{
	$head = "<h1>HTML Ipsum Presents (" . $maxblocks . " blocks)</h1>";
	$htmlstr = '	       
<p><strong>Pellentesque habitant morbi tristique</strong> senectus et netus et malesuada fames ac turpis egestas. Vestibulum tortor quam, feugiat vitae, ultricies eget, tempor sit amet, ante. Donec eu libero sit amet quam egestas semper. <em>Aenean ultricies mi vitae est.</em> Mauris placerat eleifend leo. Quisque sit amet est et sapien ullamcorper pharetra. Vestibulum erat wisi, condimentum sed, <code>commodo vitae</code>, ornare sit amet, wisi. Aenean fermentum, elit eget tincidunt condimentum, eros ipsum rutrum orci, sagittis tempus lacus enim ac dui. <a href="#">Donec non enim</a> in turpis pulvinar facilisis. Ut felis.</p>
<!--break-->
<h2>Header Level 2</h2>
	       
<ol>
   <li>Lorem ipsum dolor sit amet, consectetuer adipiscing elit.</li>
   <li>Aliquam tincidunt mauris eu risus.</li>
</ol>
<!--break-->
<blockquote><p>Lorem ipsum dolor sit amet, consectetur adipiscing elit. Vivamus magna. Cras in mi at felis aliquet congue. Ut a est eget ligula molestie gravida. Curabitur massa. Donec eleifend, libero at sagittis mollis, tellus est malesuada tellus, at luctus turpis elit sit amet quam. Vivamus pretium ornare est.</p></blockquote>
<!--break-->
<h3>Header Level 3</h3>

<ul>
   <li>Lorem ipsum dolor sit amet, consectetuer adipiscing elit.</li>
   <li>Aliquam tincidunt mauris eu risus.</li>
</ul>
<!--break-->
<pre><code>
#header h1 a { 
	display: block; 
	width: 300px; 
	height: 80px; 
}
</code></pre>
<!--break-->
<table summary="Table summary">
   <caption>Table Caption</caption>
   <thead>
      <tr>
         <th>Header</th>
         <th>Header</th>
         <th>Header</th>
      </tr>
   </thead>
   <tbody>
      <tr>
         <td>Content</td>
         <td>1</td>
         <td>a</td>
      </tr>
      <tr>
         <td>Content</td>
         <td>2</td>
         <td>b</td>
      </tr>
   </tbody>
</table><table summary="Table summary">
   <caption>Table Caption</caption>
   <thead>
      <tr>
         <th>Header</th>
         <th>Header</th>
         <th>Header</th>
      </tr>
   </thead>
   <tbody>
      <tr>
         <td>Content</td>
         <td>1</td>
         <td>a</td>
      </tr>
      <tr>
         <td>Content</td>
         <td>2</td>
         <td>b</td>
      </tr>
   </tbody>
</table>
<!--break-->
<p>Pellentesque habitant morbi tristique senectus et netus et malesuada fames ac turpis egestas. Vestibulum tortor quam, feugiat vitae, ultricies eget, tempor sit amet, ante. Donec eu libero sit amet quam egestas semper. Aenean ultricies mi vitae est. Mauris placerat eleifend leo. Quisque sit amet est et sapien ullamcorper pharetra. Vestibulum erat wisi, condimentum sed, commodo vitae, ornare sit amet, wisi. Aenean fermentum, elit eget tincidunt condimentum, eros ipsum rutrum orci, sagittis tempus lacus enim ac dui. Donec non enim in turpis pulvinar facilisis. Ut felis. Praesent dapibus, neque id cursus faucibus, tortor neque egestas augue, eu vulputate magna eros eu erat. Aliquam erat volutpat. Nam dui mi, tincidunt quis, accumsan porttitor, facilisis luctus, metus</p>
<!--break-->
<dl>
   <dt>Definition list</dt>
   <dd>Consectetur adipisicing elit, sed do eiusmod tempor incididunt ut labore et dolore magna 
aliqua. Ut enim ad minim veniam, quis nostrud exercitation ullamco laboris nisi ut aliquip ex ea 
commodo consequat.</dd>
   <dt>Lorem ipsum dolor sit amet</dt>
   <dd>Consectetur adipisicing elit, sed do eiusmod tempor incididunt ut labore et dolore magna 
aliqua. Ut enim ad minim veniam, quis nostrud exercitation ullamco laboris nisi ut aliquip ex ea 
commodo consequat.</dd>
</dl>
<!--break-->
<p>Pellentesque habitant morbi tristique senectus et netus et malesuada fames ac turpis egestas. Vestibulum tortor quam, feugiat vitae, ultricies eget, tempor sit amet, ante. Donec eu libero sit amet quam egestas semper. Aenean ultricies mi vitae est. Mauris placerat eleifend leo.</p>';

	$htmlstr = explode("<!--break-->", $htmlstr);
	$blocks = count($htmlstr);
	
	if ($maxblocks > count($htmlstr)) { $maxblocks = $blocks+1; }

	$out = "";
	
	for($x = 0; $x < $maxblocks; $x++)
	{
		$out .= $htmlstr[rand(0, $blocks)] . "\n\n";
	}
	
	return $out;
}

// generate random text content
function demodata_generate_text($maxlength)
{
	$len = rand(0, $maxlength);

	$str = 'Lorem ipsum dolor sit amet, consectetur adipiscing elit. Donec tincidunt dignissim risus. Sed mattis quam non ante. Nullam condimentum fringilla nibh. Quisque non lectus. Phasellus est orci, fermentum sit amet, vulputate sit amet, facilisis varius, nulla. In aliquet. Sed justo lectus, tempor eleifend, vulputate nec, auctor ac, nibh. Cras sem turpis, adipiscing quis, ultricies et, blandit vitae, justo. Suspendisse potenti. Quisque dapibus neque. Nulla purus sapien, interdum ac, lobortis quis, tempus quis, tellus. Cras semper odio id purus. Nunc eros. In massa. Curabitur viverra, felis eget sagittis consectetur, neque neque fringilla lectus, quis faucibus risus tellus consequat nunc. In nec lectus. Aliquam erat volutpat. Ut vel odio. Quisque lacus. Etiam consectetur rutrum justo.

Duis facilisis. Aliquam sagittis. Proin consectetur egestas metus. Curabitur pellentesque posuere arcu. Integer lorem nulla, congue a, rhoncus nec, vestibulum a, ante. Mauris lobortis iaculis erat. Maecenas faucibus tincidunt dui. Cras accumsan vestibulum ligula. Morbi dapibus, lorem nec euismod pharetra, augue risus congue augue, molestie eleifend lacus magna nec magna. Morbi ac lorem.

Aliquam id ipsum. Sed mauris. Cum sociis natoque penatibus et magnis dis parturient montes, nascetur ridiculus mus. Proin ac ipsum. Nunc vitae sapien sit amet tellus eleifend hendrerit. Phasellus orci nunc, fermentum eu, ultrices quis, dapibus vitae, lectus. Donec blandit ligula vitae justo. Quisque rutrum ante ac nisi. Sed at mi. Donec eget odio in est tempus malesuada. Donec sem. Donec tortor. In auctor purus a lectus. Nulla a leo. Curabitur velit. In et lorem. Quisque congue, lorem sed cursus ullamcorper, mauris urna adipiscing enim, eget pulvinar est lacus ut augue.

Aliquam erat volutpat. Proin scelerisque lectus non purus. Donec quis tellus. Vestibulum dictum imperdiet lacus. Integer magna libero, feugiat id, tincidunt eu, venenatis vel, mi. Cras mi. Vestibulum est. Pellentesque habitant morbi tristique senectus et netus et malesuada fames ac turpis egestas. Sed ut magna quis felis congue commodo. Fusce dictum hendrerit dolor. Donec commodo euismod dolor. Curabitur nibh. Vestibulum ut risus. Integer laoreet, ipsum congue congue suscipit, ipsum est molestie dolor, consectetur tempus justo lectus ac mauris.

Integer vulputate molestie quam. Vestibulum et nisi. Nullam in magna quis libero posuere vestibulum. Donec mi leo, elementum ut, tincidunt at, condimentum eu, est. Donec sed nisl ac justo consectetur viverra. Nulla volutpat est vitae nisl. Nullam aliquam ipsum a arcu facilisis adipiscing. Pellentesque cursus. Cum sociis natoque penatibus et magnis dis parturient montes, nascetur ridiculus mus. Nulla gravida viverra urna.

Aenean condimentum, arcu sit amet volutpat ullamcorper, erat sapien tempor dolor, ac consectetur diam ante at nibh. Proin erat lectus, vehicula id, consequat quis, semper ac, felis. Sed in lectus. Duis nec massa. Nullam augue. Duis dolor felis, porta et, molestie vitae, imperdiet eget, purus. Mauris iaculis. In cursus, neque eu sollicitudin ullamcorper, odio mauris tempus odio, id tincidunt metus leo vel ipsum. Quisque suscipit. Class aptent taciti sociosqu ad litora torquent per conubia nostra, per inceptos himenaeos. Nullam sed libero. Nunc sollicitudin diam ac dui. Nunc faucibus auctor tortor. Quisque ipsum sem, hendrerit accumsan, congue ut, porttitor ut, turpis. Sed sollicitudin, leo et condimentum tempus, massa augue dictum est, iaculis posuere nulla felis quis tortor. Pellentesque ac nisl vitae nunc porta pretium. Praesent id erat. Cras leo. Quisque eleifend metus nec lorem.

Sed sed quam non mauris aliquam rhoncus. Vestibulum ante ipsum primis in faucibus orci luctus et ultrices posuere cubilia Curae; Praesent quam turpis, dignissim et, consequat in, porttitor eget, orci. Aenean pretium, orci vel ultrices dapibus, ipsum metus lobortis ante, ac tincidunt urna erat et tortor. Mauris nisl eros, dapibus et, tristique eget, dignissim non, lacus. Fusce aliquam, turpis quis varius blandit, purus elit sollicitudin leo, vitae posuere odio odio id dui. Fusce adipiscing. Maecenas a enim eu sem accumsan laoreet. Donec sem eros, egestas ornare, fermentum quis, malesuada sed, risus. Sed eleifend faucibus magna. Fusce malesuada ante eget massa. Donec consectetur dolor vitae erat. Lorem ipsum dolor sit amet, consectetur adipiscing elit. Maecenas elit risus, pellentesque sed, imperdiet quis, pulvinar in, odio.

Duis nulla diam, fringilla at, feugiat et, tincidunt consectetur, magna. Duis id est ac neque mollis ullamcorper. Nulla quis urna. Ut vitae nisi sit amet lectus blandit ultrices. Vestibulum ante ipsum primis in faucibus orci luctus et ultrices posuere cubilia Curae; Phasellus consectetur nunc at sapien. Ut pellentesque laoreet diam. Morbi arcu neque, congue rhoncus, sollicitudin ut, condimentum id, lectus. Nulla facilisi. Praesent neque lacus, pretium eu, molestie at, laoreet convallis, metus. Fusce leo nisi, ornare ut, ullamcorper ac, molestie et, quam. In vehicula arcu eu risus. Fusce ultrices lectus sit amet diam. Maecenas commodo risus eu diam. Maecenas at nibh. Nullam mattis pharetra dolor. Donec eleifend leo sit amet diam. Quisque ante. Aliquam erat volutpat.

Maecenas vel lectus. Maecenas convallis lorem at risus. Nullam facilisis tortor. Quisque quis turpis. Suspendisse consectetur nisl. Integer facilisis, massa consectetur mattis sollicitudin, lectus elit pharetra eros, ut dignissim sapien nunc eu nisi. Nunc tortor. Vestibulum imperdiet. Aliquam erat volutpat. Integer fermentum tincidunt nisl. Vivamus fringilla, augue vel consectetur convallis, justo leo rutrum dui, et dapibus ipsum nulla sit amet est. Curabitur augue. Sed nec magna vel est auctor porttitor. Integer at lorem. Sed et turpis nec lorem consectetur volutpat. Donec vestibulum cursus mauris. Nullam blandit urna quis nibh. Proin sollicitudin elementum tellus. Suspendisse mauris enim, ultricies ut, rhoncus non, sodales at, arcu.

Nam eu tortor. Nam venenatis congue nibh. Donec posuere lacinia neque. Pellentesque vehicula. Nam eleifend ipsum. Vestibulum lectus diam, viverra vitae, tempor sit amet, eleifend eu, quam. Aenean aliquam ornare tellus. Donec vel ligula ut mauris pellentesque mattis. In neque leo, porta non, rutrum vel, sollicitudin a, libero. Pellentesque ornare mauris id odio. Aliquam erat volutpat. Aenean eget ante eget nunc feugiat lacinia. Duis ullamcorper consequat risus. Nunc at magna.

Aliquam in elit vitae dui gravida venenatis. Cum sociis natoque penatibus et magnis dis parturient montes, nascetur ridiculus mus. Aliquam bibendum risus id nunc. Nullam tempus molestie eros. Maecenas molestie pharetra augue. In sagittis enim vitae libero. Praesent feugiat blandit lacus. Lorem ipsum dolor sit amet, consectetur adipiscing elit. Maecenas erat lacus, sollicitudin placerat, facilisis nec, tempor nec, elit. Pellentesque iaculis urna ut leo mattis sagittis. Cras urna. Vestibulum malesuada orci eget leo.

Etiam egestas auctor sapien. Cum sociis natoque penatibus et magnis dis parturient montes, nascetur ridiculus mus. Praesent feugiat. Donec vehicula, dui nec adipiscing ultricies, lorem urna eleifend arcu, eu congue libero lectus ac lacus. Lorem ipsum dolor sit amet, consectetur adipiscing elit. Praesent pulvinar pellentesque lacus. Suspendisse urna orci, rutrum a, placerat a, suscipit a, est. Aliquam ac odio. Fusce arcu lorem, sodales et, condimentum ultrices, fringilla ut, tellus. Quisque velit justo, molestie ac, imperdiet et, vulputate semper, dolor. Phasellus laoreet, sapien in molestie gravida, velit quam consequat enim, sit amet malesuada arcu justo vitae turpis. Aliquam orci lorem, ornare ut, congue nec, bibendum at, erat. Donec vitae nisl id risus euismod viverra. Pellentesque eleifend risus sit amet diam. Mauris pharetra ornare tellus. Pellentesque ante elit, vestibulum eget, laoreet dictum, pulvinar vitae, velit. Mauris vel augue quis enim ornare imperdiet.

Phasellus sed tellus eget lacus molestie laoreet. Phasellus commodo euismod mauris. Sed facilisis nulla a est. Proin sit amet lectus. Morbi suscipit libero a nisl. Sed quam ipsum, ullamcorper non, congue vitae, ornare non, nisl. Nunc ultrices. Aliquam imperdiet velit sit amet nulla. Sed a neque. Fusce tempus tortor ut diam. Quisque sagittis lacus eget velit. Quisque augue magna, commodo in, molestie nec, convallis at, tortor. Etiam blandit ultrices tortor. Aliquam nisi risus, lobortis vitae, elementum pulvinar, viverra vel, dolor. Donec metus urna, faucibus aliquet, egestas in, adipiscing sit amet, risus. Vestibulum ante ipsum primis in faucibus orci luctus et ultrices posuere cubilia Curae; Proin nisi. Nunc hendrerit nisi lobortis purus.

Vivamus turpis ante, ultrices scelerisque, elementum id, tempor at, quam. Cras eu mauris eu nulla congue convallis. Fusce ornare, nibh sit amet porta rhoncus, nibh metus tincidunt magna, quis gravida dui neque pharetra risus. Etiam a felis. Fusce sed dolor. Mauris lectus mi, fringilla tempor, varius sit amet, placerat a, velit. Suspendisse justo. In vehicula urna fringilla neque. Suspendisse cursus, magna a imperdiet pellentesque, lacus velit dignissim urna, vel suscipit massa enim ac nunc. Proin porta aliquet eros. Curabitur ut erat. Quisque vitae tortor.

Duis imperdiet, mi eget euismod fermentum, odio nisl posuere quam, sit amet tristique urna diam at lacus. Duis congue lacus non ipsum. Donec felis tortor, lacinia at, rhoncus id, scelerisque ornare, nisi. Mauris felis ligula, pharetra vitae, posuere eget, tincidunt at, turpis. Donec eget ligula. Praesent fermentum dictum nisl. Phasellus enim. Nam placerat. Ut dignissim est nec lorem. Aliquam eros augue, rutrum et, placerat in, euismod in, libero. Morbi venenatis, eros non gravida rhoncus, leo metus venenatis augue, vitae dignissim lorem sapien eget diam. Ut nisl. Fusce quis lorem. Etiam mollis risus. Integer luctus. In quis quam. Pellentesque habitant morbi tristique senectus et netus et malesuada fames ac turpis egestas metus.';

	$out = substr($str, 0, $len);

	return $out;
}
?>