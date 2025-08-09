#!/bin/bash

# Exit on any error
set -e

# Step 1: Run build
echo "Running build..."
npm run build

# Step 2: Add public/build folder to git
echo "Adding public/build to git..."
git add public/build

# Step 3: Get current date and time
CURRENT_DATETIME=$(date +"%Y-%m-%d %H:%M:%S")

# Step 4: Commit
echo "Committing with message: Publish $CURRENT_DATETIME"
git commit -m "Publish $CURRENT_DATETIME"

# Optional Step 5: Push to current branch
# echo "Pushing to remote..."
# git push

echo "Build Successfully!"