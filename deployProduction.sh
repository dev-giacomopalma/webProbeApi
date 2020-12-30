 #!/bin/bash
 echo "#### Deploying testing ####"
 rsync -auv --exclude 'vendor' --exclude='bin' * root@139.162.151.157:/root/testing.webProbeApi
 echo "#### Running tests ####"
 ssh root@139.162.151.157 'cd testing.webProbeApi && ./bin/phpunit'
 echo "#### Tests over ####"
 read -p "Do you want to deploy production now? " -n 1 -r
 echo    # (optional) move to a new line
 if [[ $REPLY =~ ^[Yy]$ ]]
 then
 	 echo "#### Deploying production ####"
     rsync -auv --exclude 'vendor' --exclude='bin' * root@139.162.151.157:/root/webProbeApi
     ssh root@139.162.151.157 'cd webProbeApi && ./bin/phpunit'
 fi
 echo "#### done ####"
