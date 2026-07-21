<template>
  <div class="surveys-list">
    <div class="header-actions">
      <h2>All Surveys</h2>
      <el-button type="primary" @click="$router.push('/surveys/new')">Create New Survey</el-button>
    </div>
    <el-card>
      <el-empty description="No surveys found. Create one to get started!"></el-empty>
    </el-card>
  </div>
</template>

<style scoped>
.header-actions {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 20px;
}
</style>
