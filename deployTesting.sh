 #!/bin/bash
 echo #### Deploying testing ####
 rsync -auv --exclude 'vendor' --exclude='bin' * root@139.162.151.157:/root/testing.webProbeApi
 echo #### Running tests ####
 ssh root@139.162.151.157 'cd testing.webProbeApi && ./bin/phpunit'
 echo #### Tests over ####
