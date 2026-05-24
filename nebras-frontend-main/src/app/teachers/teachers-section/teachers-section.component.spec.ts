import { ComponentFixture, TestBed } from '@angular/core/testing';

import { TeachersSectionComponent } from './teachers-section.component';

describe('TeachersSectionComponent', () => {
  let component: TeachersSectionComponent;
  let fixture: ComponentFixture<TeachersSectionComponent>;

  beforeEach(async () => {
    await TestBed.configureTestingModule({
      imports: [TeachersSectionComponent]
    })
    .compileComponents();

    fixture = TestBed.createComponent(TeachersSectionComponent);
    component = fixture.componentInstance;
    fixture.detectChanges();
  });

  it('should create', () => {
    expect(component).toBeTruthy();
  });
});
