echo "1/ User creation"
r=`curl -s -o /dev/null -w "%{http_code}" http://localhost/mn-server/user/ -X PUT --data @user_creation -i`
if [[ $r -ne "200" ]]; then
	echo "### Error on test 1 : $r"
	exit 1;
fi
echo " => $r"

echo "2/ User creation (same info) - should fail"
echo "TODO"
#r=`curl -s -o /dev/null -w "%{http_code}" http://localhost/mn-server/user/ -X PUT --data @user_creation -i`
#if [[ $? -eq "200" ]]; then
#	echo "### Error on test 2 : $r"
#	exit 1;
#fi
#echo " => $r"

echo "3/ Manga get"
r=`curl -s -o /dev/null -w "%{http_code}" http://localhost/mn-server/manga/ -X GET --data @manga_info1 -u vuzi:1234`
if [[ $r -ne "200" ]]; then
	echo "### Error on test 3 : $r"
	exit 1;
fi
echo " => $r"

echo "4/ Manga get 2"
r=`curl -s -o /dev/null -w "%{http_code}" http://localhost/mn-server/manga/ -X GET --data @manga_info3 -u vuzi:1234`
if [[ $r -ne "200" ]]; then
	echo "### Error on test 4 : $r"
	exit 1;
fi
echo " => $r"

echo "5/ Manga get - should fail"
r=`curl -s -o /dev/null -w "%{http_code}" http://localhost/mn-server/manga/ -X GET --data @manga_info2 -u vuzi:1234`
if [[ $r -eq "200" ]]; then
	echo "### Error on test 5 : $r"
	exit 1;
fi
echo " => $r"

echo "6/ Manga add"
r=`curl -s -o /dev/null -w "%{http_code}" http://localhost/mn-server/user/manga/ -X PUT --data @manga_info1 -u vuzi:1234`
if [[ $r -ne "200" ]]; then
	echo "### Error on test 6 : $r"
	exit 1;
fi
echo " => $r"

echo "7/ Manga add (same manga) - should fail"
r=`curl -s -o /dev/null -w "%{http_code}" http://localhost/mn-server/user/manga/ -X PUT --data @manga_info1 -u vuzi:1234`
if [[ $r -eq "200" ]]; then
	echo "### Error on test 7 : $r"
	exit 1;
fi
echo " => $r"

echo "8/ Manga get from user"
r=`curl -s -o /dev/null -w "%{http_code}" http://localhost/mn-server/user/manga/1 -X GET -u vuzi:1234`
if [[ $r -ne "200" ]]; then
	echo "### Error on test 8 : $r"
	exit 1;
fi
echo " => $r"
